<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\Order;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Template invoice default (markdown). Disimpan di tbl_appconfig dengan
     * key "invoice_template" dan bisa diedit admin. Token {var} diisi per order.
     */
    public const DEFAULT_TEMPLATE = <<<'MD'
# Invoice #{order_id}

| Field | Nilai |
|-------|-------|
| **Pelanggan** | {customer_name} ({username}) |
| **Paket** | {plan} |
| **Harga** | {price} |
| **Status** | {status} |
| **Dibuat** | {created_at} |
| **Dibayar** | {paid_at} |

{payment_section}

---

{company_name} · {company_address} · {company_phone} · {company_email}
MD;

    /** Variabel yang tersedia untuk template invoice (label → deskripsi). */
    public const VARS = [
        'order_id' => 'Nomor/ID order',
        'customer_name' => 'Nama lengkap customer',
        'username' => 'Username customer',
        'plan' => 'Nama paket',
        'price' => 'Harga paket (format Rupiah)',
        'status' => 'Status order (Pending/Paid/Cancelled)',
        'created_at' => 'Tanggal order dibuat',
        'paid_at' => 'Tanggal dibayar',
        'company_name' => 'Nama perusahaan (dari Pengaturan Umum)',
        'company_address' => 'Alamat perusahaan',
        'company_phone' => 'Telepon perusahaan',
        'company_email' => 'Email perusahaan',
        'payment_section' => 'Blok pembayaran otomatis (QR + instruksi / konfirmasi)',
    ];

    public function show(Order $order): Response
    {
        $order->load('customer', 'plan');

        $template = AppConfig::get('invoice_template', self::DEFAULT_TEMPLATE) ?: self::DEFAULT_TEMPLATE;
        $html = Str::markdown(strtr($template, $this->buildData($order)));

        return Inertia::render('Public/Invoice', [
            'content' => $html,
            'order' => $order,
            'qrPath' => AppConfig::get('payment_qr_path'),
        ]);
    }

    /** Siapkan nilai variabel per order. */
    private function buildData(Order $order): array
    {
        $paymentSection = match ($order->status) {
            'pending' => $this->pendingSection(),
            'paid' => '> ✅ Pembayaran sudah dikonfirmasi.',
            'cancelled' => '> Order ini dibatalkan.',
            default => '',
        };

        $statusLabel = ucfirst((string) $order->status);

        $values = [
            'order_id' => (string) $order->id,
            'customer_name' => $order->customer->fullname ?? '',
            'username' => $order->customer->username ?? '',
            'plan' => $order->plan->name_plan ?? '',
            'price' => 'Rp '.number_format((float) $order->price, 0, ',', '.'),
            'status' => $statusLabel,
            'created_at' => $order->created_at?->format('d M Y H:i') ?? '-',
            'paid_at' => $order->paid_at?->format('d M Y H:i') ?? '-',
            'company_name' => AppConfig::get('company_name', 'NetRadius'),
            'company_address' => AppConfig::get('company_address'),
            'company_phone' => AppConfig::get('company_phone'),
            'company_email' => AppConfig::get('company_email'),
        ];

        $data = [];
        foreach (array_keys(self::VARS) as $var) {
            $data['{'.$var.'}'] = (string) ($values[$var] ?? '');
        }
        $data['{payment_section}'] = $paymentSection;

        return $data;
    }

    /** Blok pembayaran untuk order pending: QR + instruksi. */
    private function pendingSection(): string
    {
        $qrPath = AppConfig::get('payment_qr_path');
        $image = $qrPath
            ? "\n![QR Pembayaran](/storage/{$qrPath})"
            : "\n_QR pembayaran belum diatur oleh admin._";

        $instructions = AppConfig::get('payment_instructions')
            ?: 'Silakan lakukan pembayaran, admin akan konfirmasi secara manual.';

        return "{$image}\n\n{$instructions}";
    }
}
