<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
</head>
<body>
    <h1>Invoice</h1>
    <p>Customer: {{ $order->customer->fullname }}</p>
    <p>Paket: {{ $order->plan->name_plan }}</p>
    <p>Harga: Rp {{ number_format((float) $order->price, 0, ',', '.') }}</p>
    <p>Status: {{ ucfirst($order->status) }}</p>

    @if ($order->status === 'pending')
        @if ($qrPath)
            <img src="{{ asset('storage/'.$qrPath) }}" alt="QR Pembayaran" width="300">
        @else
            <p>QR pembayaran belum diatur oleh admin.</p>
        @endif
        <p>Silakan lakukan pembayaran, admin akan konfirmasi secara manual.</p>
    @endif
</body>
</html>
