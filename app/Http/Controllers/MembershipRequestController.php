<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\BookReservation;
use App\Models\LibrarianRegistrationRequest;
use App\Models\MembershipRequest;
use App\Models\Pengembalian;
use App\Models\Pinjam;
use App\Models\RenewalRequest;
use App\Support\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MembershipRequestController extends Controller
{
    public function index(Request $request)
    {
        $membershipRequests = MembershipRequest::with(['user', 'anggota', 'processedBy'])
            ->latest()
            ->get()
            ->map(fn ($item) => $this->mapMembershipRequest($item));

        $renewalRequests = RenewalRequest::with(['user', 'anggota', 'borrowing.book', 'processedBy'])
            ->latest()
            ->get()
            ->map(fn ($item) => $this->mapRenewalRequest($item));

        $reservationRequests = BookReservation::with(['user', 'anggota', 'book'])
            ->latest()
            ->get()
            ->map(fn ($item) => $this->mapReservationRequest($item));

        $librarianRequests = collect();
        if (Schema::hasTable('librarian_registration_requests')) {
            $librarianRequests = LibrarianRegistrationRequest::with(['user', 'processedBy'])
                ->latest()
                ->get()
                ->map(fn ($item) => $this->mapLibrarianRequest($item));
        }

        $merged = Collection::make()
            ->concat($reservationRequests)
            ->concat($renewalRequests)
            ->concat($membershipRequests)
            ->concat($librarianRequests)
            ->sortByDesc('created_at')
            ->values();

        $page = (int) $request->get('page', 1);
        $perPage = 12;
        $paginated = new LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('membership-requests.index', ['requests' => $paginated]);
    }

    public function reservations()
    {
        BookReservation::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $reservations = BookReservation::with(['book.rack', 'anggota', 'user'])
            ->latest()
            ->paginate(12);

        return view('reservations.index', compact('reservations'));
    }

    public function cancellations()
    {
        $requests = MembershipRequest::with(['user', 'anggota', 'processedBy'])
            ->where('type', 'cancellation')
            ->latest()
            ->paginate(10);

        return view('membership-requests.cancellations', compact('requests'));
    }

    public function renewals()
    {
        $requests = RenewalRequest::with(['user', 'anggota', 'borrowing.book', 'processedBy'])
            ->latest()
            ->paginate(10);

        return view('renewal-requests.index', compact('requests'));
    }

    public function renewalsShow(RenewalRequest $renewalRequest)
    {
        $renewalRequest->load(['user', 'anggota', 'borrowing.book', 'processedBy']);

        return view('membership-requests.renewal-show', compact('renewalRequest'));
    }

    public function reservationsShow(BookReservation $reservation)
    {
        if ($reservation->status === 'pending' && $reservation->expires_at && $reservation->expires_at->isPast()) {
            $reservation->update(['status' => 'expired']);
        }

        $reservation->load(['book.rack', 'anggota', 'user']);

        return view('membership-requests.reservation-show', compact('reservation'));
    }

    public function librarianRegistrations()
    {
        $requests = Schema::hasTable('librarian_registration_requests')
            ? LibrarianRegistrationRequest::with(['user', 'processedBy'])->latest()->paginate(10)
            : collect();

        return view('membership-requests.librarian-registrations', compact('requests'));
    }

    public function librarianRegistrationsShow(LibrarianRegistrationRequest $librarianRegistrationRequest)
    {
        abort_unless(Schema::hasTable('librarian_registration_requests'), 404);

        $librarianRegistrationRequest->load(['user', 'processedBy']);

        return view('membership-requests.librarian-registration-show', compact('librarianRegistrationRequest'));
    }

    public function show($id)
    {
        $membershipRequest = MembershipRequest::with(['user', 'anggota', 'processedBy'])->findOrFail($id);

        return view('membership-requests.show', ['membershipRequest' => $membershipRequest]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $user = auth()->user();
        $memberProfile = $user?->memberProfile;

        if (! $memberProfile) {
            return back()->withErrors(['profile' => 'Profil anggota tidak ditemukan.']);
        }

        $anggota = $user?->anggota ?? Anggota::where('id_anggota', $memberProfile->id_anggota)->first();

        if (! $anggota) {
            return back()->withErrors(['profile' => 'Data anggota tidak ditemukan.']);
        }

        $hasActiveBorrowings = Pinjam::where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($hasActiveBorrowings) {
            return back()->withErrors(['active' => 'Tidak dapat membatalkan keanggotaan karena masih ada buku yang dipinjam.']);
        }

        $hasUnpaidFines = Pengembalian::where('anggota_id', $anggota->id)
            ->where('denda', '>', 0)
            ->whereHas('fine', function ($query) {
                $query->where('status', 'unpaid');
            })
            ->exists();

        if ($hasUnpaidFines) {
            return back()->withErrors(['fine' => 'Tidak dapat membatalkan keanggotaan karena masih ada denda yang belum diselesaikan.']);
        }

        $existingPending = MembershipRequest::where('user_id', $user->id)
            ->where('type', 'cancellation')
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->withErrors(['request' => 'Permintaan pembatalan masih menunggu persetujuan pustakawan.']);
        }

        MembershipRequest::create([
            'user_id' => $user->id,
            'anggota_id' => $anggota->id,
            'type' => 'cancellation',
            'status' => 'pending',
            'reason' => $validated['reason'],
        ]);

        $memberProfile->update(['membership_status' => 'pending_cancellation']);

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan telah diajukan.');
    }

    public function cancelOwnPending(Request $request)
    {
        $user = $request->user();

        $membershipRequest = MembershipRequest::where('user_id', $user?->id)
            ->where('type', 'cancellation')
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        DB::transaction(function () use ($membershipRequest, $user) {
            $membershipRequest->delete();
            $user?->memberProfile?->update(['membership_status' => 'active']);
        });

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan berhasil dibatalkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $membershipRequest = MembershipRequest::with(['user.memberProfile', 'anggota.libraryCard'])->findOrFail($id);

        DB::transaction(function () use ($validated, $membershipRequest) {
            $membershipRequest->status = $validated['status'];
            $membershipRequest->processed_at = now();
            $membershipRequest->processed_by = auth()->id();
            $membershipRequest->notes = $validated['notes'] ?? null;
            $membershipRequest->save();

            $memberProfile = $membershipRequest->user?->memberProfile;
            $anggota = $membershipRequest->anggota;

            if (! $memberProfile || ! $anggota) {
                return;
            }

            if ($membershipRequest->type === 'renewal') {
                if ($validated['status'] === 'approved') {
                    $libraryCard = $anggota->libraryCard;
                    if ($libraryCard) {
                        $baseDate = $libraryCard->expiry_date && $libraryCard->expiry_date->isFuture()
                            ? $libraryCard->expiry_date->copy()
                            : now();

                        $libraryCard->update([
                            'status' => 'active',
                            'expiry_date' => $baseDate->addYear()->toDateString(),
                        ]);
                    }

                    $memberProfile->update(['membership_status' => 'active']);
                }
            } elseif ($validated['status'] === 'approved') {
                $memberProfile->update(['membership_status' => 'cancelled']);
                $anggota->libraryCard()?->delete();
                $membershipRequest->user?->delete();
            } else {
                $memberProfile->update(['membership_status' => 'active']);
            }

            if ($membershipRequest->user_id) {
                NotificationHelper::send(
                    $membershipRequest->user_id,
                    'membership_' . $membershipRequest->type . '_' . $validated['status'],
                    $membershipRequest->type === 'renewal'
                        ? ($validated['status'] === 'approved' ? 'Perpanjangan membership disetujui' : 'Perpanjangan membership ditolak')
                        : ($validated['status'] === 'approved' ? 'Pembatalan keanggotaan disetujui' : 'Pembatalan keanggotaan ditolak'),
                    $membershipRequest->type === 'renewal'
                        ? ($validated['status'] === 'approved'
                            ? 'Permintaan perpanjangan membership kamu telah disetujui.'
                            : 'Permintaan perpanjangan membership kamu ditolak.' . (($validated['notes'] ?? null) ? ' Catatan: ' . $validated['notes'] : ''))
                        : ($validated['status'] === 'approved'
                            ? 'Permintaan pembatalan keanggotaan kamu telah disetujui.'
                            : 'Permintaan pembatalan keanggotaan kamu ditolak.' . (($validated['notes'] ?? null) ? ' Catatan: ' . $validated['notes'] : '')),
                    [
                        'membership_request_id' => $membershipRequest->id,
                        'status' => $validated['status'],
                    ]
                );
            }
        });

        return redirect()->route('membership-requests.index')->with('success', 'Permintaan keanggotaan telah diproses!');
    }

    private function mapMembershipRequest(MembershipRequest $item): array
    {
        return [
            'kind' => 'membership',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $item->anggota?->nama ?? $item->user?->name ?? '-',
            'member_code' => $item->anggota?->id_anggota ?? '-',
            'title' => $item->type === 'renewal' ? 'Perpanjangan Membership' : 'Pembatalan Keanggotaan',
            'description' => $item->reason ?? '-',
            'created_at' => $item->created_at,
            'detail_url' => route('membership-requests.show', $item->id),
        ];
    }

    private function mapRenewalRequest(RenewalRequest $item): array
    {
        return [
            'kind' => 'renewal',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $item->anggota?->nama ?? $item->user?->name ?? '-',
            'member_code' => $item->anggota?->id_anggota ?? '-',
            'title' => 'Perpanjangan Peminjaman',
            'description' => 'Buku: ' . ($item->borrowing?->book?->judul ?? '-'),
            'created_at' => $item->created_at,
            'detail_url' => route('membership-requests.renewals.show', $item),
        ];
    }

    private function mapReservationRequest(BookReservation $item): array
    {
        return [
            'kind' => 'reservation',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $item->anggota?->nama ?? $item->user?->name ?? '-',
            'member_code' => $item->anggota?->id_anggota ?? '-',
            'title' => 'Reservasi Buku',
            'description' => 'Buku: ' . ($item->book?->judul ?? '-'),
            'created_at' => $item->created_at,
            'detail_url' => route('membership-requests.reservations.show', $item),
        ];
    }

    private function mapLibrarianRequest(LibrarianRegistrationRequest $item): array
    {
        return [
            'kind' => 'librarian',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $item->user?->name ?? '-',
            'member_code' => '-',
            'title' => 'Pengajuan Librarian',
            'description' => $item->reason ?: 'Tidak ada alasan tambahan.',
            'created_at' => $item->created_at,
            'detail_url' => route('membership-requests.librarian-registrations.show', $item),
        ];
    }
}
