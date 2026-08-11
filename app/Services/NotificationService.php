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
}
