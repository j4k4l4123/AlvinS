<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
<h1>Register</h1>

@if($errors->any())
    <div style="color:red;">
        <ul>
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register.post') }}">
    @csrf

    <div>
        <label>Name</label>
        <input name="name" value="{{ old('name') }}" required>
    </div>
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>
    </div>
    <div>
        <label>Role</label>
        <select name="role" required>
            <option value="member" {{ old('role')==='member'?'selected':'' }}>Member</option>
            <option value="librarian" {{ old('role')==='librarian'?'selected':'' }}>Librarian</option>
        </select>
    </div>

    <button type="submit">Create account</button>
</form>

<p><a href="{{ route('login') }}">Already have an account? Login</a></p>
</body>
</html>

