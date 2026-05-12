@extends('layouts.app')

@section('title', 'Cancel Membership - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Cancel Membership</h1>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-card" style="max-width:720px;">
    <p class="text-muted">Submit a cancellation request. This will be reviewed by a librarian.</p>

    <form method="POST" action="{{ route('membership-requests.store') }}" style="margin-top:20px;">
        @csrf
        <div class="form-group">
            <label for="reason">Reason</label>
            <textarea name="reason" id="reason" rows="5" class="search-input" required>{{ old('reason') }}</textarea>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-submit">Submit Request</button>
            <a href="{{ route('member.dashboard') }}" class="btn-cancel">Back</a>
        </div>
    </form>
</div>
@endsection
