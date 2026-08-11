<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dashboard Pelanggan</title>
</head>
<body>
    <h1>Halo, {{ $customer->fullname }}</h1>
    <p>Status: {{ $customer->status }}</p>

    <h2>Riwayat Order</h2>
    <ul>
        @forelse ($orders as $order)
            <li>
                {{ $order->plan->name_plan }} — Rp {{ number_format((float) $order->price, 0, ',', '.') }}
                — {{ ucfirst($order->status) }}
                — <a href="{{ route('invoice.show', $order->invoice_token) }}">Lihat Invoice</a>
            </li>
        @empty
            <li>Belum ada order.</li>
        @endforelse
    </ul>

    <h2>Riwayat Transaksi</h2>
    <ul>
        @forelse ($transactions as $transaction)
            <li>
                {{ $transaction->plan_name }} — Rp {{ number_format((float) $transaction->price, 0, ',', '.') }}
                — {{ $transaction->recharged_on }}
            </li>
        @empty
            <li>Belum ada transaksi.</li>
        @endforelse
    </ul>

    <form method="POST" action="{{ route('customer.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
