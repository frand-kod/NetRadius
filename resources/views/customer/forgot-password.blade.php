<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lupa Password - Pelanggan</title>
</head>
<body>
    <h1>Lupa Password</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif
    @if (session('new_password'))
        <p>Password baru Anda: <strong>{{ session('new_password') }}</strong></p>
    @endif
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>1. Minta kode verifikasi</h2>
    <form method="POST" action="{{ route('customer.forgot-password.request') }}">
        @csrf
        <label>
            Username
            <input type="text" name="username" value="{{ old('username', session('username')) }}" required>
        </label>
        <button type="submit">Kirim Kode via WhatsApp</button>
    </form>

    <h2>2. Masukkan kode & reset password</h2>
    <form method="POST" action="{{ route('customer.forgot-password.reset') }}">
        @csrf
        <label>
            Username
            <input type="text" name="username" value="{{ old('username', session('username')) }}" required>
        </label>
        <label>
            Kode Verifikasi
            <input type="text" name="otp" required>
        </label>
        <button type="submit">Reset Password</button>
    </form>

    <p><a href="{{ route('customer.login') }}">Kembali ke Login</a></p>
</body>
</html>
