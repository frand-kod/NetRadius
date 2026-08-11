<?php

namespace App\Http\Controllers;

use App\Exceptions\RadiusRejectedException;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\UserRecharge;
use App\Models\Voucher;
use App\Services\Radius\RadiusIdentityResolver;
use App\Services\Radius\RadiusReplyAttributesBuilder;
use App\Services\RechargeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class RadiusAuthController extends Controller
{
    public function __construct(
        private readonly RadiusIdentityResolver $identityResolver,
        private readonly RadiusReplyAttributesBuilder $attributesBuilder,
        private readonly RechargeService $rechargeService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $action = $request->header('X-FreeRadius-Section') ?: $request->input('action');

        try {
            return match ($action) {
                'authenticate' => $this->authenticate($request),
                'authorize' => $this->authorize($request),
                'accounting' => $this->accounting($request),
                default => $this->reject("Invalid Command : {$action}"),
            };
        } catch (RadiusRejectedException $e) {
            return $this->reject($e->getMessage(), $e->httpCode, $e->extraAttributes);
        } catch (Throwable $e) {
            report($e);

            return $this->reject("Command Failed : {$action}");
        }
    }

    private function authenticate(Request $request): JsonResponse
    {
        $identity = $this->identityResolver->resolve($request);

        $found = $identity->username === $identity->password
            ? Voucher::query()->where('code', $identity->username)->exists()
            : Customer::query()
                ->where('status', 'Active')
                ->where(function ($query) use ($identity) {
                    $query->where(function ($q) use ($identity) {
                        $q->where('username', $identity->username)->where('password', $identity->password);
                    })->orWhere(function ($q) use ($identity) {
                        $q->where('pppoe_username', $identity->username)->where('pppoe_password', $identity->password);
                    });
                })
                ->exists();

        if (! $found) {
            throw new RadiusRejectedException('Login invalid......', 401, ['control:Auth-Type' => 'Reject']);
        }

        return response()->json(null, 204);
    }

    private function authorize(Request $request): JsonResponse
    {
        $identity = $this->identityResolver->resolve($request, correctPppoeUsername: true);

        $recharge = UserRecharge::query()->where('username', $identity->username)->first();

        if (! $recharge) {
            $pppoeCustomer = Customer::query()->where('pppoe_username', $identity->username)->first();
            if ($pppoeCustomer) {
                $identity->username = $pppoeCustomer->username;
                $recharge = UserRecharge::query()->where('username', $identity->username)->first();
            }
        }

        if ($recharge) {
            if (! $identity->isVoucher && ! $identity->isChap) {
                $customer = Customer::query()
                    ->where('status', 'Active')
                    ->where(function ($query) use ($identity) {
                        $query->where('username', $identity->username)
                            ->orWhere('pppoe_username', $identity->username);
                    })
                    ->first();

                $matches = $customer
                    && ($customer->password === $identity->password || $customer->pppoe_password === $identity->password);

                if (! $matches) {
                    throw new RadiusRejectedException('Username or Password is wrong');
                }
            }

            $attrs = $this->attributesBuilder->build($recharge, $request->input('framedIPAddress'));

            return response()->json($attrs, 200);
        }

        if (! $identity->isVoucher) {
            throw new RadiusRejectedException('Internet Plan Expired..');
        }

        $voucher = Voucher::query()
            ->where('code', $identity->username)
            ->where('routers', 'radius')
            ->first();

        if (! $voucher) {
            throw new RadiusRejectedException('Invalid Voucher..');
        }

        if ($voucher->status != 0) {
            throw new RadiusRejectedException('Voucher Expired...');
        }

        $plan = Plan::findOrFail($voucher->id_plan);
        $this->rechargeService->recharge(null, $plan, 'radius', 'Voucher', $identity->username);

        $voucher->status = '1';
        $voucher->used_date = now();
        $voucher->save();

        $recharge = UserRecharge::query()->where('username', $identity->username)->first();
        if (! $recharge) {
            throw new RadiusRejectedException('Voucher activation failed');
        }

        $attrs = $this->attributesBuilder->build($recharge, $request->input('framedIPAddress'));

        return response()->json($attrs, 200);
    }

    private function accounting(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        if ($username === '') {
            return response()->json(['control:Auth-Type' => 'Reject', 'reply:Reply-Message' => 'Username empty'], 200);
        }

        $row = DB::table('rad_acct')
            ->where('username', $username)
            ->where('macaddr', $request->input('macAddr'))
            ->where('nasid', $request->input('nasid'))
            ->first();

        $outputOctets = (int) $request->input('acctOutputOctets', 0);
        $inputOctets = (int) $request->input('acctInputOctets', 0);

        $data = [
            'username' => $username,
            'realm' => $request->input('realm'),
            'nasipaddress' => $request->input('nasIpAddress'),
            'acctsessiontime' => (int) $request->input('acctSessionTime', 0),
            'nasid' => $request->input('nasid'),
            'nasportid' => $request->input('nasPortId'),
            'nasporttype' => $request->input('nasPortType'),
            'framedipaddress' => $request->input('framedIPAddress'),
            'acctsessionid' => $request->input('acctSessionId'),
            'macaddr' => $request->input('macAddr'),
            'acctoutputoctets' => ($row->acctoutputoctets ?? 0) + $outputOctets,
            'acctinputoctets' => ($row->acctinputoctets ?? 0) + $inputOctets,
            'dateAdded' => now(),
        ];

        if (in_array($request->input('acctStatusType'), ['Start', 'Stop'], true)) {
            $data['acctstatustype'] = $request->input('acctStatusType');
        }

        $recharge = UserRecharge::query()
            ->where('username', $username)
            ->where('status', 'on')
            ->where('routers', 'radius')
            ->first();

        if (! $recharge) {
            $pppoeCustomer = Customer::query()->where('pppoe_username', $username)->first();
            if ($pppoeCustomer) {
                $recharge = UserRecharge::query()->where('username', $pppoeCustomer->username)->first();
            }
        }

        if ($recharge) {
            if ($row) {
                DB::table('rad_acct')->where('id', $row->id)->update($data);
            } else {
                DB::table('rad_acct')->insert($data);
            }

            $attrs = $this->attributesBuilder->build($recharge, $request->input('framedIPAddress'));

            return response()->json($attrs, 200);
        }

        return response()->json(['control:Auth-Type' => 'Accept', 'reply:Reply-Message' => 'Saved'], 200);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function reject(string $message, int $code = 401, array $extra = []): JsonResponse
    {
        return response()->json(array_merge($extra, ['Reply-Message' => $message]), $code);
    }
}
