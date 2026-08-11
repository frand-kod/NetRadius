<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login Pelanggan</title>
</head>
<body>
    <h1>Login Pelanggan</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('customer.login.submit') }}">
        @csrf
        <label>
            Username
            <input type="text" name="username" value="{{ old('username') }}" required autofocus>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Login</button>
    </form>
</body>
</html>
