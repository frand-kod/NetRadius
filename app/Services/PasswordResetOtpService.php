<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetOtpService
{
    private const TTL_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly NotificationService $notifications) {}

    /**
     * Request an OTP for the given guard ("customer" or "admin"). Always
     * no-ops silently when the username isn't found or a code was already
     * requested recently — callers should show a generic "if found" message
     * regardless, to avoid username enumeration (mirrors the legacy
     * `forgot.php` behaviour).
     */
    public function requestOtp(string $guard, string $username): void
    {
        $user = $this->findUser($guard, $username);
        if (! $user) {
            return;
        }

        $phone = $guard === 'customer' ? $user->phonenumber : $user->phone;
        if (empty($phone) || strlen($phone) <= 5) {
            return;
        }

        $cacheKey = $this->cacheKey($guard, $username);
        if (Cache::has($cacheKey)) {
            return;
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put($cacheKey, ['otp' => $otp, 'attempts' => self::MAX_ATTEMPTS], now()->addMinutes(self::TTL_MINUTES));

        $this->notifications->sendWhatsapp($phone, "Kode verifikasi reset password Anda: {$otp} (berlaku ".self::TTL_MINUTES.' menit)');
    }

    /**
     * Verify the OTP and, if valid, reset the password to a new random
     * value. Returns the new plaintext password to show the user (Customer
     * stores it plaintext directly; User/admin gets it hashed before save,
     * the plaintext is only returned once for display), or null if the OTP
     * was missing/expired/wrong (attempts are decremented on wrong code).
     */
    public function verifyAndReset(string $guard, string $username, string $otp): ?string
    {
        $cacheKey = $this->cacheKey($guard, $username);
        $data = Cache::get($cacheKey);
        if (! $data) {
            return null;
        }

        if (! hash_equals($data['otp'], $otp)) {
            $data['attempts']--;
            if ($data['attempts'] <= 0) {
                Cache::forget($cacheKey);
            } else {
                Cache::put($cacheKey, $data, now()->addMinutes(self::TTL_MINUTES));
            }

            return null;
        }

        Cache::forget($cacheKey);

        $user = $this->findUser($guard, $username);
        if (! $user) {
            return null;
        }

        $newPassword = Str::password(10);
        $user->password = $guard === 'customer' ? $newPassword : Hash::make($newPassword);
        $user->save();

        return $newPassword;
    }

    private function findUser(string $guard, string $username): Customer|User|null
    {
        return $guard === 'customer'
            ? Customer::query()->where('username', $username)->first()
            : User::query()->where('username', $username)->first();
    }

    private function cacheKey(string $guard, string $username): string
    {
        return "forgot-password:{$guard}:".sha1($username);
    }
}
