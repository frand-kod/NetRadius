<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function sendTelegram(string $text, ?string $chatId = null): void
    {
        $botToken = AppConfig::get('telegram_bot');
        if (empty($botToken)) {
            return;
        }

        $chatId ??= AppConfig::get('telegram_target_id');

        try {
            $response = Http::get("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            $this->logMessage('Telegram', (string) $chatId, $text, $response->successful() ? 'Success' : 'Error', $response->successful() ? null : $response->body());
        } catch (Throwable $e) {
            report($e);
            $this->logMessage('Telegram', (string) $chatId, $text, 'Error', $e->getMessage());
        }
    }

    /**
     * Send via GOWA (self-hosted WhatsApp gateway), see `docs/gowa-wa-gateway.md`
     * `POST /send/message` — not a generic URL-template GET gateway.
     */
    public function sendWhatsapp(string $phone, string $text): void
    {
        $serverUrl = AppConfig::get('alt_wga_server_url');
        if (empty($serverUrl)) {
            return;
        }

        $jid = $this->formatPhone($phone).'@s.whatsapp.net';

        try {
            $response = Http::withBasicAuth(
                (string) AppConfig::get('alt_wga_username'),
                (string) AppConfig::get('alt_wga_password'),
            )
                ->withHeaders(['X-Device-Id' => AppConfig::get('alt_wga_device_id')])
                ->post(rtrim($serverUrl, '/').'/send/message', [
                    'phone' => $jid,
                    'message' => $text,
                ]);

            $success = $response->successful() && ($response->json('code') === 'SUCCESS');

            $this->logMessage('WhatsApp', $jid, $text, $success ? 'Success' : 'Error', $success ? null : $response->body());
        } catch (Throwable $e) {
            report($e);
            $this->logMessage('WhatsApp', $jid ?? $phone, $text, 'Error', $e->getMessage());
        }
    }

    /**
     * Port of `Lang::phoneFormat()`: strip leading 0, prepend country code.
     */
    private function formatPhone(string $phone): string
    {
        if (! ctype_digit($phone)) {
            return $phone;
        }

        $countryCode = AppConfig::get('country_code_phone', '62');

        return preg_replace('/^0/', $countryCode, $phone);
    }

    private function logMessage(string $type, string $recipient, string $content, string $status, ?string $errorMessage = null): void
    {
        try {
            MessageLog::create([
                'message_type' => $type,
                'recipient' => $recipient,
                'message_content' => $content,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to write message log: {$e->getMessage()}");
        }
    }

    /**
     * Kirim pesan uji ke Telegram. @return array{success: bool, message: string}
     */
    public function testTelegram(string $token, string $chatId): array
    {
        if ($token === '' || $chatId === '') {
            return ['success' => false, 'message' => 'Bot Token dan Chat ID wajib diisi.'];
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => 'Uji koneksi NetRadius: notifikasi Telegram berfungsi.',
            ]);

            if ($response->successful() && $response->json('ok')) {
                return ['success' => true, 'message' => 'Pesan uji berhasil terkirim ke Telegram.'];
            }

            return ['success' => false, 'message' => 'Gagal: '.$response->json('description', 'respon tidak dikenal')];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Gagal: '.$e->getMessage()];
        }
    }

    /**
     * Kirim pesan uji via GOWA. @return array{success: bool, message: string}
     */
    public function testWhatsapp(string $serverUrl, string $deviceId, string $username, string $password, string $testPhone): array
    {
        if ($serverUrl === '') {
            return ['success' => false, 'message' => 'Server URL wajib diisi.'];
        }
        if ($testPhone === '') {
            return ['success' => false, 'message' => 'Nomor tujuan uji wajib diisi.'];
        }

        $jid = $this->formatPhone($testPhone).'@s.whatsapp.net';

        try {
            $response = Http::withBasicAuth($username, $password)
                ->withHeaders(['X-Device-Id' => $deviceId])
                ->post(rtrim($serverUrl, '/').'/send/message', [
                    'phone' => $jid,
                    'message' => 'Uji koneksi NetRadius: notifikasi WhatsApp berfungsi.',
                ]);

            if ($response->successful() && $response->json('code') === 'SUCCESS') {
                return ['success' => true, 'message' => 'Pesan uji berhasil terkirim via WhatsApp.'];
            }

            return ['success' => false, 'message' => 'Gagal: '.$response->body()];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Gagal: '.$e->getMessage()];
        }
    }

    /**
     * @return array{configured: bool, status: string, info: string}
     */
    public function telegramStatus(): array
    {
        $token = AppConfig::get('telegram_bot');
        if ($token === '') {
            return ['configured' => false, 'status' => 'not-configured', 'info' => 'Bot token belum diatur'];
        }

        try {
            $response = Http::get("https://api.telegram.org/bot{$token}/getMe");
            if ($response->successful() && $response->json('ok')) {
                $username = $response->json('result.username', '');

                return ['configured' => true, 'status' => 'connected', 'info' => $username !== '' ? '@'.$username : 'Bot terhubung'];
            }

            return ['configured' => true, 'status' => 'error', 'info' => $response->json('description', 'Token tidak valid')];
        } catch (Throwable $e) {
            return ['configured' => true, 'status' => 'error', 'info' => $e->getMessage()];
        }
    }

    /**
     * @return array{configured: bool, status: string, info: string}
     */
    public function whatsappStatus(): array
    {
        $serverUrl = AppConfig::get('alt_wga_server_url');
        if ($serverUrl === '') {
            return ['configured' => false, 'status' => 'not-configured', 'info' => 'Server URL belum diatur'];
        }

        try {
            Http::timeout(3)->get(rtrim($serverUrl, '/').'/health');
        } catch (Throwable $e) {
            return ['configured' => true, 'status' => 'error', 'info' => 'Server tidak dapat dijangkau'];
        }

        $deviceId = AppConfig::get('alt_wga_device_id');
        if ($deviceId === '') {
            return ['configured' => true, 'status' => 'server-up', 'info' => 'Server aktif, Device ID belum diatur'];
        }

        try {
            $response = Http::withBasicAuth((string) AppConfig::get('alt_wga_username'), (string) AppConfig::get('alt_wga_password'))
                ->withHeaders(['X-Device-Id' => $deviceId])
                ->get(rtrim($serverUrl, '/').'/app/status');

            $results = $response->json('results');
            if ($results && ($results['is_connected'] ?? false) && ($results['is_logged_in'] ?? false)) {
                return ['configured' => true, 'status' => 'connected', 'info' => 'Device terhubung & login'];
            }
            if ($results && ($results['is_logged_in'] ?? false)) {
                return ['configured' => true, 'status' => 'connected', 'info' => 'Device login (belum terhubung)'];
            }

            return ['configured' => true, 'status' => 'error', 'info' => 'Device tidak terhubung / belum login'];
        } catch (Throwable $e) {
            return ['configured' => true, 'status' => 'error', 'info' => 'Gagal cek status device'];
        }
    }
}
