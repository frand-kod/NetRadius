<?php

namespace App\Services;

use App\Models\AppConfig;

class NotificationTemplateService
{
    /**
     * Registri template pesan otomatis.
     *
     * Kunci = id peristiwa; nilai = metadata (label/deskripsi), template default,
     * dan daftar variabel yang tersedia. Template tersimpan di tbl_appconfig
     * dengan prefix "notif_". Token placeholder memakai sintaks {var}.
     */
    public const EVENTS = [
        'order_created' => [
            'label' => 'Order Dibuat (ke Customer)',
            'description' => 'Dikirim ke WhatsApp customer saat sebuah order baru dibuat.',
            'default' => "Order baru dibuat untuk paket {plan} (Rp{price}).\nSilakan lakukan pembayaran dan lihat invoice di:\n{invoice_url}",
            'vars' => [
                'customer_name' => 'Nama lengkap customer',
                'username' => 'Username customer',
                'plan' => 'Nama paket',
                'price' => 'Harga paket (format Rupiah)',
                'invoice_url' => 'Tautan invoice pembayaran',
            ],
        ],
        'order_created_admin' => [
            'label' => 'Order Dibuat (Notif Admin)',
            'description' => 'Notifikasi Telegram untuk admin saat order baru dibuat.',
            'default' => "#order_baru\nCustomer: {customer_name} ({username})\nPaket: {plan}\nHarga: Rp{price}\nInvoice: {invoice_url}",
            'vars' => [
                'customer_name' => 'Nama lengkap customer',
                'username' => 'Username customer',
                'plan' => 'Nama paket',
                'price' => 'Harga paket (format Rupiah)',
                'invoice_url' => 'Tautan invoice pembayaran',
            ],
        ],
        'recharge_success' => [
            'label' => 'Recharge Sukses (ke Customer)',
            'description' => 'Dikirim ke WhatsApp customer setelah paket berhasil diaktifkan/diperpanjang.',
            'default' => "Paket {plan} berhasil {action}.\nBerlaku sampai: {expires_at}",
            'vars' => [
                'customer_name' => 'Nama lengkap customer',
                'username' => 'Username customer',
                'plan' => 'Nama paket',
                'action' => '"diaktifkan" (baru) atau "diperpanjang"',
                'expires_at' => 'Tanggal & jam masa berlaku',
                'price' => 'Harga paket (format Rupiah)',
            ],
        ],
        'recharge_success_admin' => [
            'label' => 'Recharge Sukses (Notif Admin)',
            'description' => 'Notifikasi Telegram untuk admin saat paket berhasil diaktifkan.',
            'default' => "#recharge\nUsername: {username}\nPaket: {plan}\nHarga: Rp{price}\nMetode: {method}\nBerlaku sampai: {expires_at}",
            'vars' => [
                'customer_name' => 'Nama lengkap customer',
                'username' => 'Username customer',
                'plan' => 'Nama paket',
                'price' => 'Harga paket (format Rupiah)',
                'method' => 'Metode pembayaran',
                'expires_at' => 'Tanggal & jam masa berlaku',
            ],
        ],
        'expired' => [
            'label' => 'Paket Expired (ke Customer)',
            'description' => 'Dikirim ke WhatsApp customer saat paket dinyatakan kedaluwarsa.',
            'default' => 'Paket {plan} Anda sudah expired pada {expired_at}. Silakan hubungi admin untuk perpanjangan.',
            'vars' => [
                'customer_name' => 'Nama lengkap customer',
                'username' => 'Username customer',
                'plan' => 'Nama paket',
                'expired_at' => 'Tanggal kedaluwarsa',
            ],
        ],
        'reminder' => [
            'label' => 'Pengingat Expired (ke Customer)',
            'description' => 'Pengingat sebelum paket kedaluwarsa (H-7, H-3, H-1).',
            'default' => 'Pengingat: paket {plan} Anda akan expired dalam {days_left} hari ({expired_at}).',
            'vars' => [
                'customer_name' => 'Nama lengkap customer',
                'username' => 'Username customer',
                'plan' => 'Nama paket',
                'days_left' => 'Sisa hari (1, 3, atau 7)',
                'expired_at' => 'Tanggal kedaluwarsa',
            ],
        ],
        'otp' => [
            'label' => 'Kode OTP (Reset Password)',
            'description' => 'Dikirim ke WhatsApp customer/admin saat meminta kode reset password.',
            'default' => 'Kode verifikasi reset password Anda: {otp} (berlaku {ttl} menit)',
            'vars' => [
                'otp' => 'Kode verifikasi 6 digit',
                'ttl' => 'Masa berlaku kode (menit)',
                'username' => 'Username yang meminta reset',
            ],
        ],
    ];

    /**
     * Cek apakah sebuah peristiwa notifikasi aktif. Tersimpan di AppConfig
     * dengan key "notif_<event>_enabled" ('1'/'0'), default aktif.
     */
    public function isEnabled(string $event): bool
    {
        return AppConfig::get('notif_'.$event.'_enabled', '1') !== '0';
    }

    /**
     * Render pesan untuk sebuah peristiwa dengan mengisi placeholder.
     * Menggunakan template tersimpan di AppConfig, atau default jika kosong.
     * Token yang tidak dikenal dibiarkan apa adanya.
     */
    public function render(string $event, array $data = []): string
    {
        $config = self::EVENTS[$event] ?? null;
        if (! $config) {
            return '';
        }

        $template = AppConfig::get('notif_'.$event, $config['default']) ?: $config['default'];

        $replace = [];
        foreach ($config['vars'] as $var => $description) {
            $replace['{'.$var.'}'] = (string) ($data[$var] ?? '');
        }

        return strtr($template, $replace);
    }
}
