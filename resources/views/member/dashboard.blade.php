@extends('layouts.app')
@section('title', 'Member Dashboard - PerpusKu')
@section('body-class', 'dashboard-page')
@section('content')
<div class='page-header'>
    <h1>Member Dashboard</h1>
    <p class='text-muted'>Welcome back, {{ auth()->user()->name }}</p>
</div>
@if(\C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1)
<div class='items-grid' style='margin-bottom: 30px;'>
    <div class='item-card' style='grid-column: span 2;'>
        <div class='tilt-layer'><div class='item-body'>
            <h3 class='item-title'>My Profile</h3>
            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;'>
                <div><strong>ID:</strong> {{ \C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->id_anggota ?? '-' }}</div>
                <div><strong>Name:</strong> {{ \C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->nama ?? '-' }}</div>
                <div><strong>Address:</strong> {{ \C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->alamat ?? '-' }}</div>
                <div><strong>Phone:</strong> {{ \C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->no_tlp ?? '-' }}</div>
                <div><strong>Registered:</strong> {{ \C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->tanggal_daftar?->format('d/m/Y') ?? '-' }}</div>
                <div><strong>Status:</strong> <span class='status-badge {{ \C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->membership_status === " cancelled\ ? \status-borrowed\ : \status-available\ }}'>{{ ucfirst(\C:\Users\ALVIN DWI LINTANG H\OneDrive\Documents\WindowsPowerShell\Microsoft.PowerShell_profile.ps1->membership_status ?? 'active') }}</span></div>
 </div>
 </div></div>
 </div>
</div>
@endif
<div class='items-grid' style='margin-bottom: 30px;'>
 <a href='{{ route(\books.index\) }}' class='item-card'><div class='tilt-layer'><div class='item-body'><h3 class='item-title'>Browse Books</h3><p class='item-detail'>View available books</p></div></div></a>
 <a href='{{ route(\pinjam.index\) }}' class='item-card'><div class='tilt-layer'><div class='item-body'><h3 class='item-title'>My Borrowings</h3><p class='item-detail'>View active loans</p></div></div></a>
 <a href='{{ route(\member.history\) }}' class='item-card'><div class='tilt-layer'><div class='item-body'><h3 class='item-title'>Borrowing History</h3><p class='item-detail'>Past returns & fines</p></div></div></a>
 <a href='{{ route(\member.library-card\) }}' class='item-card'><div class='tilt-layer'><div class='item-body'><h3 class='item-title'>My Library Card</h3><p class='item-detail'>View card details</p></div></div></a>
</div>
<div class='content-card' style='padding: 20px; margin-bottom: 20px;'>
 <h2 style='margin-bottom: 15px;'>Active Borrowings</h2>
 @if(\->count() > 0)
 <div class='table-responsive'><table><thead><tr><th>#</th><th>Book</th><th>Borrowed On</th><th>Due Date</th><th>Status</th></tr></thead><tbody>
 @foreach(\ as \)
 <tr><td>{{ \->iteration }}</td><td>{{ \->book?->judul ?? '-' }}</td><td>{{ \->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td><td>{{ \->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
 <td>@if(\->isOverdue())<span class='status-badge' style='background: var(--pu-danger);'>Overdue ({{ \->daysOverdue() }} days)</span>@else<span class='status-badge status-available'>On Time</span>@endif</td></tr>
 @endforeach
 </tbody></table></div>
 @else
 <div class='empty-state'><div class='empty-icon'>??</div><h3>No active borrowings</h3><p class='text-muted'>You haven't borrowed any books yet.</p><a href='{{ route(\books.index\) }}' class='btn-add'><span class='icon'>+</span> Browse Books</a></div>
 @endif
</div>
<div class='content-card' style='padding: 20px;'>
 <h2 style='margin-bottom: 15px;'>Borrowing History</h2>
 @if(\->count() > 0)
 <div class='table-responsive'><table><thead><tr><th>#</th><th>Book</th><th>Borrowed</th><th>Returned</th><th>Fine</th></tr></thead><tbody>
 @foreach(\ as \)
 <tr><td>{{ \->iteration }}</td><td>{{ \->book?->judul ?? '-' }}</td><td>{{ \->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
 <td>{{ \->status === 'dikembalikan' ? (\->pengembalian?->tanggal_dikembalikan?->format('d/m/Y') ?? '-') : 'Not returned' }}</td>
 <td>@if(\->pengembalian && \->pengembalian->denda > 0)<span style='color: var(--pu-danger); font-weight: bold;'>Rp {{ number_format(\->pengembalian->denda, 0, ',', '.') }}</span>@else<span style='color: var(--pu-success);'>Paid</span>@endif</td></tr>
 @endforeach
 </tbody></table></div>
 @else
 <div class='empty-state'><div class='empty-icon'>??</div><h3>No borrowing history</h3></div>
 @endif
</div>
@endsection
