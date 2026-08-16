<?php

namespace Tests\Unit;

use App\Services\Radius\ChapAuthenticator;
use PHPUnit\Framework\TestCase;

class ChapAuthenticatorTest extends TestCase
{
    public function test_correct_password_returns_true(): void
    {
        $password = 'secret123';
        $id = "\x7f";
        $challenge = hex2bin('0102030405060708090a0b0c0d0e0f10');

        [$chapPassword, $chapChallenge] = $this->chapCredentials($id, $password, $challenge);

        $this->assertTrue(ChapAuthenticator::verify($password, $chapPassword, $chapChallenge));
    }

    public function test_incorrect_password_returns_false(): void
    {
        $password = 'secret123';
        $id = "\x7f";
        $challenge = hex2bin('0102030405060708090a0b0c0d0e0f10');

        [$chapPassword, $chapChallenge] = $this->chapCredentials($id, $password, $challenge);

        $this->assertFalse(ChapAuthenticator::verify('wrongpass', $chapPassword, $chapChallenge));
    }

    public function test_different_challenge_changes_the_response(): void
    {
        $password = 'secret123';
        $id = "\x7f";

        $challengeA = hex2bin('0102030405060708090a0b0c0d0e0f10');
        $challengeB = hex2bin('000102030405060708090a0b0c0d0e0f');

        $credA = $this->chapCredentials($id, $password, $challengeA);
        $credB = $this->chapCredentials($id, $password, $challengeB);

        $this->assertTrue(ChapAuthenticator::verify($password, ...$credA));
        $this->assertTrue(ChapAuthenticator::verify($password, ...$credB));
    }

    /**
     * Build a CHAP-Password/CHAP-Challenge pair for a given id/password/challenge.
     *
     * Per RFC 2865 the CHAP response is MD5(ID + password + challenge), and the
     * CHAP-Password attribute carries the 1-byte ID followed by the 16-byte
     * response. FreeRADIUS forwards these as "0x"-prefixed hex strings, matching
     * the format the resolver produces.
     *
     * @return array{0: string, 1: string} [$chapPassword, $chapChallenge]
     */
    private function chapCredentials(string $id, string $password, string $challenge): array
    {
        $responseHex = md5($id.$password.$challenge);

        return [
            '0x'.bin2hex($id).$responseHex,
            '0x'.bin2hex($challenge),
        ];
    }
}
