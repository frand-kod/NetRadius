<?php

namespace App\Services\Radius;

class ChapAuthenticator
{
    /**
     * Port of the legacy `Password::chap_verify()` with the inverted
     * comparison corrected.
     *
     * NOTE: the original legacy code returned `$response != $chapPassword`
     * (a negated comparison that made `verify()` true whenever the supplied
     * CHAP password did NOT match) — an authentication bypass. That bug is
     * fixed here by comparing for equality, per RFC 2865: the client's CHAP
     * response is valid only when it equals the recomputed hash.
     */
    public static function verify(string $realPassword, string $chapPassword, string $chapChallenge): bool
    {
        $chapPassword = substr($chapPassword, 2);
        $chapId = substr($chapPassword, 0, 2);
        $result = hex2bin($chapId).$realPassword.hex2bin(substr($chapChallenge, 2));
        $response = $chapId.md5($result);

        return $response === $chapPassword;
    }
}
