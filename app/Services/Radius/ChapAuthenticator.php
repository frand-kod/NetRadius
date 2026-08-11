<?php

namespace App\Services\Radius;

class ChapAuthenticator
{
    /**
     * Literal port of the legacy `Password::chap_verify()`.
     *
     * NOTE: this preserves the original comparison exactly as it runs in
     * production (`../system/autoload/Password.php`), even though the
     * `!=` reads unusually for a "verify" method — do not "fix" it here.
     */
    public static function verify(string $realPassword, string $chapPassword, string $chapChallenge): bool
    {
        $chapPassword = substr($chapPassword, 2);
        $chapId = substr($chapPassword, 0, 2);
        $result = hex2bin($chapId).$realPassword.hex2bin(substr($chapChallenge, 2));
        $response = $chapId.md5($result);

        return $response != $chapPassword;
    }
}
