@extends('layouts.app')

@section('title', 'Issue Library Card - PerpusKu')

@section('content')
<div class="page-header">
    <h1>Issue Library Card</h1>
</div>

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="content-card" style="max-width:720px;">
    <form method="POST" action="{{ route('library-cards.store') }}">
        @csrf
        <div class="form-group">
            <label for="anggota_id">Member</label>
            <select name="anggota_id" id="anggota_id" class="search-input" required>
                <option value="">Select a member</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" {{ old('anggota_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->nama }} ({{ $member->id_anggota }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-top:16px;">
            <label for="expiry_date">Expiry Date</label>
            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="search-input" required>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-submit">Create Card</button>
            <a href="{{ route('library-cards.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
@endsection
