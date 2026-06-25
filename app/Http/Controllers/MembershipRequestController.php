<?php

namespace App\Http\Controllers;

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
        $membershipRows = DB::table('membership_requests')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        $renewalRows = DB::table('renewal_requests')
            ->orderByDesc('created_at')
            ->get();

        $reservationRows = DB::table('book_reservations')
            ->orderByDesc('created_at')
            ->get();

        $librarianRows = collect();
        if (Schema::hasTable('librarian_registration_requests')) {
            $librarianRows = DB::table('librarian_registration_requests')
                ->orderByDesc('created_at')
                ->get();
        }

        $membershipRequests = $membershipRows->map(function ($row) {
            $mapped = $this->mapMembershipRequest((object) $row);
            return array_merge($mapped, [
                'created_at' => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
            ]);
        });

        $renewalRequests = $renewalRows->map(function ($row) {
            $mapped = $this->mapRenewalRequest((object) $row);
            return array_merge($mapped, [
                'created_at' => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
            ]);
        });

        $reservationRequests = $reservationRows->map(function ($row) {
            $mapped = $this->mapReservationRequest((object) $row);
            return array_merge($mapped, [
                'created_at' => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
            ]);
        });

        $librarianRequests = $librarianRows->map(function ($row) {
            $mapped = $this->mapLibrarianRequest((object) $row);
            return array_merge($mapped, [
                'created_at' => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
            ]);
        });

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
        DB::table('book_reservations')
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $reservations = DB::table('book_reservations')
            ->leftJoin('books', 'book_reservations.book_id', '=', 'books.id')
            ->leftJoin('racks', 'books.rack_id', '=', 'racks.id')
            ->leftJoin('anggota', 'book_reservations.anggota_id', '=', 'anggota.id')
            ->leftJoin('users', 'book_reservations.user_id', '=', 'users.id')
            ->select([
                'book_reservations.*',
                'books.judul as book_judul',
                'racks.name as rack_name',
                'anggota.nama as anggota_nama',
                'users.name as user_name'
            ])
            ->orderByDesc('book_reservations.created_at')
            ->paginate(12, ['*'], 'reservation_page');

        $reservations->getCollection()->transform(function ($item) {
            $item->book = (object) [
                'judul' => $item->book_judul ?? '-',
                'rack' => $item->rack_name ? (object) ['name' => $item->rack_name] : null
            ];
            $item->anggota = (object) ['nama' => $item->anggota_nama ?? '-'];
            $item->user = (object) ['name' => $item->user_name ?? '-'];
            $item->expires_at = $item->expires_at ? \Carbon\Carbon::parse($item->expires_at) : null;
            return $item;
        });

        return view('reservations.index', compact('reservations'));
    }

    public function cancellations()
    {
        $requests = DB::table('membership_requests')
            ->leftJoin('users', 'membership_requests.user_id', '=', 'users.id')
            ->leftJoin('anggota', 'membership_requests.anggota_id', '=', 'anggota.id')
            ->leftJoin('users as processed_users', 'membership_requests.processed_by', '=', 'processed_users.id')
            ->select([
                'membership_requests.*',
                'users.name as user_name',
                'anggota.nama as anggota_nama',
                'processed_users.name as processed_by_name'
            ])
            ->where('membership_requests.type', 'cancellation')
            ->whereNull('membership_requests.deleted_at')
            ->orderByDesc('membership_requests.created_at')
            ->paginate(10, ['*'], 'cancel_page');

        $requests->getCollection()->transform(function ($item) {
            $item->user = (object) ['name' => $item->user_name ?? '-'];
            $item->anggota = (object) ['nama' => $item->anggota_nama ?? '-'];
            $item->processedBy = $item->processed_by_name ? (object) ['name' => $item->processed_by_name] : null;
            if (isset($item->created_at)) {
                $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            }
            return $item;
        });

        return view('membership-requests.cancellations', compact('requests'));
    }

    public function renewals()
    {
        $requests = DB::table('renewal_requests')
            ->leftJoin('users', 'renewal_requests.user_id', '=', 'users.id')
            ->leftJoin('anggota', 'renewal_requests.anggota_id', '=', 'anggota.id')
            ->leftJoin('pinjam', 'renewal_requests.pinjam_id', '=', 'pinjam.id')
            ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
            ->leftJoin('users as processed_users', 'renewal_requests.processed_by', '=', 'processed_users.id')
            ->select([
                'renewal_requests.*',
                'users.name as user_name',
                'anggota.nama as anggota_nama',
                'books.judul as book_judul',
                'pinjam.tanggal_kembali as pinjam_tanggal_kembali',
                'pinjam.tanggal_pinjam as pinjam_tanggal_pinjam',
                'processed_users.name as processed_by_name'
            ])
            ->orderByDesc('renewal_requests.created_at')
            ->paginate(10, ['*'], 'renew_page');

        $requests->getCollection()->transform(function ($item) {
            $item->user = (object) ['name' => $item->user_name ?? '-'];
            $item->anggota = (object) ['nama' => $item->anggota_nama ?? '-'];
            $item->borrowing = (object) [
                'book' => (object) ['judul' => $item->book_judul ?? '-'],
                'tanggal_kembali' => $item->pinjam_tanggal_kembali ? \Carbon\Carbon::parse($item->pinjam_tanggal_kembali) : null,
                'tanggal_pinjam' => $item->pinjam_tanggal_pinjam ? \Carbon\Carbon::parse($item->pinjam_tanggal_pinjam) : null,
            ];
            $item->processedBy = $item->processed_by_name ? (object) ['name' => $item->processed_by_name] : null;
            if (isset($item->created_at)) {
                $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            }
            return $item;
        });

        return view('renewal-requests.index', compact('requests'));
    }

    public function renewalsShow($renewalRequestId)
    {
        $renewalRequest = DB::table('renewal_requests')->where('id', $renewalRequestId)->first();
        abort_if(!$renewalRequest, 404);

        $renewalRequest->user = DB::table('users')->where('id', $renewalRequest->user_id)->first();
        $renewalRequest->anggota = DB::table('anggota')->where('id', $renewalRequest->anggota_id)->first();
        $renewalRequest->processedBy = $renewalRequest->processed_by ? DB::table('users')->where('id', $renewalRequest->processed_by)->first() : null;
        $renewalRequest->borrowing = DB::table('pinjam')->where('id', $renewalRequest->pinjam_id)->first();
        if ($renewalRequest->borrowing) {
            $renewalRequest->borrowing->book = DB::table('books')->where('id', $renewalRequest->borrowing->book_id)->first();
            if ($renewalRequest->borrowing->tanggal_pinjam) {
                $renewalRequest->borrowing->tanggal_pinjam = \Carbon\Carbon::parse($renewalRequest->borrowing->tanggal_pinjam);
            }
            if ($renewalRequest->borrowing->tanggal_kembali) {
                $renewalRequest->borrowing->tanggal_kembali = \Carbon\Carbon::parse($renewalRequest->borrowing->tanggal_kembali);
            }
        }

        if ($renewalRequest->processed_at) {
            $renewalRequest->processed_at = \Carbon\Carbon::parse($renewalRequest->processed_at);
        }
        if ($renewalRequest->created_at) {
            $renewalRequest->created_at = \Carbon\Carbon::parse($renewalRequest->created_at);
        }

        return view('membership-requests.renewal-show', compact('renewalRequest'));
    }

    public function reservationsShow($reservationId)
    {
        $reservation = DB::table('book_reservations')->where('id', $reservationId)->first();
        abort_if(!$reservation, 404);

        $expiresAt = $reservation->expires_at ? \Carbon\Carbon::parse($reservation->expires_at) : null;
        if ($reservation->status === 'pending' && $expiresAt && $expiresAt->isPast()) {
            DB::table('book_reservations')->where('id', $reservationId)->update(['status' => 'expired']);
            $reservation->status = 'expired';
        }

        $reservation->book = DB::table('books')->where('id', $reservation->book_id)->first();
        if ($reservation->book && $reservation->book->rack_id) {
            $reservation->book->rack = DB::table('racks')->where('id', $reservation->book->rack_id)->first();
        } else if ($reservation->book) {
            $reservation->book->rack = null;
        }
        $reservation->anggota = DB::table('anggota')->where('id', $reservation->anggota_id)->first();
        $reservation->user = DB::table('users')->where('id', $reservation->user_id)->first();
        $reservation->expires_at = $expiresAt;

        if ($reservation->created_at) {
            $reservation->created_at = \Carbon\Carbon::parse($reservation->created_at);
        }

        return view('membership-requests.reservation-show', compact('reservation'));
    }

    public function librarianRegistrations()
    {
        if (Schema::hasTable('librarian_registration_requests')) {
            $requests = DB::table('librarian_registration_requests')
                ->leftJoin('users', 'librarian_registration_requests.user_id', '=', 'users.id')
                ->leftJoin('users as processed_users', 'librarian_registration_requests.processed_by', '=', 'processed_users.id')
                ->select([
                    'librarian_registration_requests.*',
                    'users.name as user_name',
                    'processed_users.name as processed_by_name'
                ])
                ->orderByDesc('librarian_registration_requests.created_at')
                ->paginate(10, ['*'], 'lib_page');

            $requests->getCollection()->transform(function ($item) {
                $item->user = (object) ['name' => $item->user_name ?? '-'];
                $item->processedBy = $item->processed_by_name ? (object) ['name' => $item->processed_by_name] : null;
                if (isset($item->created_at)) {
                    $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
                }
                return $item;
            });
        } else {
            $requests = collect();
        }

        return view('membership-requests.librarian-registrations', compact('requests'));
    }

    public function librarianRegistrationsShow($librarianRegistrationRequestId)
    {
        abort_unless(Schema::hasTable('librarian_registration_requests'), 404);

        $librarianRegistrationRequest = DB::table('librarian_registration_requests')
            ->where('id', $librarianRegistrationRequestId)
            ->first();
        abort_if(!$librarianRegistrationRequest, 404);

        $librarianRegistrationRequest->user = DB::table('users')->where('id', $librarianRegistrationRequest->user_id)->first();
        $librarianRegistrationRequest->processedBy = $librarianRegistrationRequest->processed_by 
            ? DB::table('users')->where('id', $librarianRegistrationRequest->processed_by)->first() 
            : null;

        if ($librarianRegistrationRequest->created_at) {
            $librarianRegistrationRequest->created_at = \Carbon\Carbon::parse($librarianRegistrationRequest->created_at);
        }
        if ($librarianRegistrationRequest->processed_at) {
            $librarianRegistrationRequest->processed_at = \Carbon\Carbon::parse($librarianRegistrationRequest->processed_at);
        }

        return view('membership-requests.librarian-registration-show', compact('librarianRegistrationRequest'));
    }

    public function show($id)
    {
        $membershipRequest = DB::table('membership_requests')->where('id', $id)->first();
        abort_if(!$membershipRequest, 404);

        $membershipRequest->user = DB::table('users')->where('id', $membershipRequest->user_id)->first();
        if ($membershipRequest->user) {
            $membershipRequest->user->memberProfile = DB::table('member_profiles')->where('user_id', $membershipRequest->user_id)->first();
        }
        $membershipRequest->anggota = DB::table('anggota')->where('id', $membershipRequest->anggota_id)->first();
        $membershipRequest->processedBy = $membershipRequest->processed_by 
            ? DB::table('users')->where('id', $membershipRequest->processed_by)->first() 
            : null;

        if ($membershipRequest->created_at) {
            $membershipRequest->created_at = \Carbon\Carbon::parse($membershipRequest->created_at);
        }
        if ($membershipRequest->processed_at) {
            $membershipRequest->processed_at = \Carbon\Carbon::parse($membershipRequest->processed_at);
        }

        return view('membership-requests.show', ['membershipRequest' => $membershipRequest]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $user = auth()->user();
        $memberProfile = $user ? DB::table('member_profiles')->where('user_id', $user->id)->first() : null;

        if (! $memberProfile) {
            return back()->withErrors(['profile' => 'Profil anggota tidak ditemukan.']);
        }

        $anggota = $user?->anggota ?? DB::table('anggota')->where('id_anggota', $memberProfile->id_anggota)->first();

        if (! $anggota) {
            return back()->withErrors(['profile' => 'Data anggota tidak ditemukan.']);
        }

        $hasActiveBorrowings = DB::table('pinjam')
            ->where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($hasActiveBorrowings) {
            return back()->withErrors(['active' => 'Tidak dapat membatalkan keanggotaan karena masih ada buku yang dipinjam.']);
        }

        $hasUnpaidFines = DB::table('fines')
            ->where('anggota_id', $anggota->id)
            ->where('status', 'unpaid')
            ->exists();

        if ($hasUnpaidFines) {
            return back()->withErrors(['fine' => 'Tidak dapat membatalkan keanggotaan karena masih ada denda yang belum diselesaikan.']);
        }

        $existingPending = DB::table('membership_requests')
            ->where('user_id', $user->id)
            ->where('type', 'cancellation')
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->exists();

        if ($existingPending) {
            return back()->withErrors(['request' => 'Permintaan pembatalan masih menunggu persetujuan pustakawan.']);
        }

        DB::table('membership_requests')->insert([
            'user_id' => $user->id,
            'anggota_id' => $anggota->id,
            'type' => 'cancellation',
            'status' => 'pending',
            'reason' => $validated['reason'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('member_profiles')->where('user_id', $user->id)->update([
            'membership_status' => 'pending_cancellation',
            'updated_at' => now(),
        ]);

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan telah diajukan.');
    }

    public function cancelOwnPending(Request $request)
    {
        $user = $request->user();

        $membershipRequest = DB::table('membership_requests')
            ->where('user_id', $user?->id)
            ->where('type', 'cancellation')
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->first();

        abort_if(!$membershipRequest, 404);

        DB::transaction(function () use ($membershipRequest, $user) {
            DB::table('membership_requests')->where('id', $membershipRequest->id)->update([
                'deleted_at' => now(),
            ]);
            DB::table('member_profiles')->where('user_id', $user->id)->update([
                'membership_status' => 'active',
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('member.dashboard')->with('success', 'Permintaan pembatalan keanggotaan berhasil dibatalkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $membershipRequest = DB::table('membership_requests')->where('id', $id)->first();
        abort_if(!$membershipRequest, 404);

        DB::transaction(function () use ($validated, $membershipRequest, $id) {
            DB::table('membership_requests')->where('id', $id)->update([
                'status' => $validated['status'],
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'updated_at' => now(),
            ]);

            $user = DB::table('users')->where('id', $membershipRequest->user_id)->first();
            $memberProfile = $user ? DB::table('member_profiles')->where('user_id', $user->id)->first() : null;
            $anggota = DB::table('anggota')->where('id', $membershipRequest->anggota_id)->first();

            if ($membershipRequest->type === 'renewal') {
                if ($validated['status'] === 'approved' && $anggota) {
                    $libraryCard = DB::table('library_cards')->where('anggota_id', $anggota->id)->first();
                    if ($libraryCard) {
                        $expiry = $libraryCard->expiry_date ? \Carbon\Carbon::parse($libraryCard->expiry_date) : null;
                        $baseDate = $expiry && $expiry->isFuture()
                            ? $expiry->copy()
                            : now();

                        DB::table('library_cards')->where('id', $libraryCard->id)->update([
                            'status' => 'active',
                            'expiry_date' => $baseDate->addYear()->toDateString(),
                            'updated_at' => now(),
                        ]);
                    }

                    if ($memberProfile) {
                        DB::table('member_profiles')->where('id', $memberProfile->id)->update([
                            'membership_status' => 'active',
                            'updated_at' => now(),
                        ]);
                    }
                }
            } elseif ($validated['status'] === 'approved') {
                if ($anggota) {
                    DB::table('library_cards')->where('anggota_id', $anggota->id)->delete();
                    DB::table('anggota')->where('id', $anggota->id)->delete();
                }
                if ($memberProfile) {
                    DB::table('member_profiles')->where('id', $memberProfile->id)->delete();
                }
                if ($user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'deleted_at' => now(),
                    ]);
                }
            } else {
                if ($memberProfile) {
                    DB::table('member_profiles')->where('id', $memberProfile->id)->update([
                        'membership_status' => 'active',
                        'updated_at' => now(),
                    ]);
                }
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

    private function mapMembershipRequest(object $item): array
    {
        $anggota = DB::table('anggota')->where('id', $item->anggota_id)->first();
        $user = DB::table('users')->where('id', $item->user_id)->first();
        return [
            'kind' => 'membership',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $anggota?->nama ?? $user?->name ?? '-',
            'member_code' => $anggota?->id_anggota ?? '-',
            'title' => $item->type === 'renewal' ? 'Perpanjangan Membership' : 'Pembatalan Keanggotaan',
            'description' => $item->reason ?? '-',
            'created_at' => $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null,
            'detail_url' => route('membership-requests.show', $item->id),
        ];
    }

    private function mapRenewalRequest(object $item): array
    {
        $anggota = DB::table('anggota')->where('id', $item->anggota_id)->first();
        $user = DB::table('users')->where('id', $item->user_id)->first();
        $borrowing = DB::table('pinjam')->where('id', $item->pinjam_id)->first();
        $book = $borrowing ? DB::table('books')->where('id', $borrowing->book_id)->first() : null;
        return [
            'kind' => 'renewal',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $anggota?->nama ?? $user?->name ?? '-',
            'member_code' => $anggota?->id_anggota ?? '-',
            'title' => 'Perpanjangan Peminjaman',
            'description' => 'Buku: ' . ($book?->judul ?? '-'),
            'created_at' => $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null,
            'detail_url' => route('membership-requests.renewals.show', $item->id),
        ];
    }

    private function mapReservationRequest(object $item): array
    {
        $anggota = DB::table('anggota')->where('id', $item->anggota_id)->first();
        $user = DB::table('users')->where('id', $item->user_id)->first();
        $book = DB::table('books')->where('id', $item->book_id)->first();
        return [
            'kind' => 'reservation',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $anggota?->nama ?? $user?->name ?? '-',
            'member_code' => $anggota?->id_anggota ?? '-',
            'title' => 'Reservasi Buku',
            'description' => 'Buku: ' . ($book?->judul ?? '-'),
            'created_at' => $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null,
            'detail_url' => route('membership-requests.reservations.show', $item->id),
        ];
    }

    private function mapLibrarianRequest(object $item): array
    {
        $user = DB::table('users')->where('id', $item->user_id)->first();
        return [
            'kind' => 'librarian',
            'id' => $item->id,
            'status' => $item->status,
            'member_name' => $user?->name ?? '-',
            'member_code' => '-',
            'title' => 'Pengajuan Librarian',
            'description' => $item->reason ?: 'Tidak ada alasan tambahan.',
            'created_at' => $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null,
            'detail_url' => route('membership-requests.librarian-registrations.show', $item->id),
        ];
    }
}
