<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Print Voucher</title>
    <style>
        body { font-family: monospace; }
        .grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .voucher {
            border: 1px dashed #000;
            padding: 10px;
            width: 220px;
            text-align: center;
        }
        .code { font-size: 20px; font-weight: bold; letter-spacing: 2px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print</button>

    <div class="grid">
        @foreach ($vouchers as $voucher)
            <div class="voucher">
                <div>{{ $voucher->plan->name_plan }}</div>
                <div class="code">{{ $voucher->code }}</div>
                <div>Router: {{ $voucher->routers }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
