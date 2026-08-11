<?php

namespace App\Services\Radius;

use App\Exceptions\RadiusRejectedException;
use App\Models\Customer;
use Illuminate\Http\Request;

class RadiusIdentityResolver
{
    /**
     * Port of the duplicated CHAP/plain identity-resolution block shared by
     * `authenticate` and `authorize` in the legacy `../radius.php`.
     *
     * @param  bool  $correctPppoeUsername  `authorize` rewrites $username to the
     *      customer's real username when matched via pppoe_username; `authenticate` does not.
     */
    public function resolve(Request $request, bool $correctPppoeUsername = false): RadiusIdentity
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $chapPassword = (string) $request->input('CHAPassword', '');
        $chapChallenge = (string) $request->input('CHAPchallenge', '');
        // `authorize` (unlike `authenticate`) seeds this from the raw
        // username===password comparison before CHAP/plain resolution runs.
        $isVoucher = $username === $password;
        $isChap = false;

        if ($chapPassword !== '') {
            $customer = Customer::query()
                ->whereRaw('username = ?', [$username])
                ->where('status', 'Active')
                ->first();

            if ($customer) {
                [$password, $isVoucher, $isChap] = $this->resolveChapBranch($username, $customer->password, $customer->pppoe_password, $chapPassword, $chapChallenge);
            } else {
                $pppoeCustomer = Customer::query()
                    ->whereRaw('pppoe_username = ?', [$username])
                    ->where('status', 'Active')
                    ->first();

                if ($pppoeCustomer) {
                    [$password, $isVoucher, $isChap] = $this->resolveChapBranch($username, $pppoeCustomer->password, $pppoeCustomer->pppoe_password, $chapPassword, $chapChallenge);
                    if ($correctPppoeUsername && ! $isVoucher) {
                        $username = $pppoeCustomer->username;
                    }
                }
            }
        } else {
            if ($username !== '' && $password === '') {
                $isVoucher = true;
                $password = $username;
            } elseif ($username === '' || $password === '') {
                throw new RadiusRejectedException('Login invalid......', 401, ['control:Auth-Type' => 'Reject']);
            }
        }

        return new RadiusIdentity($username, $password, $isVoucher, $isChap);
    }

    /**
     * @return array{0: string, 1: bool, 2: bool} [$password, $isVoucher, $isChap]
     */
    private function resolveChapBranch(string $username, string $realPassword, ?string $pppoePassword, string $chapPassword, string $chapChallenge): array
    {
        if (ChapAuthenticator::verify($realPassword, $chapPassword, $chapChallenge)) {
            return [$realPassword, false, true];
        }

        if (! empty($pppoePassword) && ChapAuthenticator::verify($pppoePassword, $chapPassword, $chapChallenge)) {
            return [$pppoePassword, false, true];
        }

        if (ChapAuthenticator::verify($username, $chapPassword, $chapChallenge)) {
            return [$username, true, false];
        }

        if (ChapAuthenticator::verify('', $chapPassword, $chapChallenge)) {
            return [$username, true, false];
        }

        throw new RadiusRejectedException('Username or Password is wrong');
    }
}
