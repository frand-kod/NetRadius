<?php

namespace App\Services\Hotspot;

use App\Models\Bandwidth;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Query;
use PEAR2\Net\RouterOS\Request;

class MikrotikHotspotService implements HotspotDeviceInterface
{
    public function addCustomer(Customer $customer, Plan $plan): void
    {
        $client = $this->client($plan->routers);
        $isExpiredProfile = Plan::where('plan_expired', $plan->id)->exists();

        $this->removeHotspotUser($client, $customer->username);
        if ($isExpiredProfile) {
            $this->removeHotspotActiveUser($client, $customer->username);
        }
        $this->addHotspotUser($client, $plan, $customer);
    }

    public function removeCustomer(Customer $customer, Plan $plan): void
    {
        $client = $this->client($plan->routers);

        if (! empty($plan->plan_expired)) {
            $expiredPlan = Plan::find($plan->plan_expired);
            if ($expiredPlan) {
                $this->addCustomer($customer, $expiredPlan);
                $this->removeHotspotActiveUser($client, $customer->username);

                return;
            }
        }

        $this->removeHotspotUser($client, $customer->username);
        $this->removeHotspotActiveUser($client, $customer->username);
    }

    public function syncCustomer(Customer $customer, Plan $plan): void
    {
        $this->addCustomer($customer, $plan);
    }

    public function changeUsername(Plan $plan, string $from, string $to): void
    {
        $client = $this->client($plan->routers);

        $request = new Request('/ip/hotspot/user/print');
        $request->setArgument('.proplist', '.id');
        $request->setQuery(Query::where('name', $from));
        $id = $client->sendSync($request)->getProperty('.id');

        if (! empty($id)) {
            $setRequest = new Request('/ip/hotspot/user/set');
            $setRequest->setArgument('numbers', $id);
            $setRequest->setArgument('name', $to);
            $client->sendSync($setRequest);
            $this->removeHotspotActiveUser($client, $from);
        }
    }

    public function addPlan(Plan $plan): void
    {
        $client = $this->client($plan->routers);

        $addRequest = new Request('/ip/hotspot/user/profile/add');
        $client->sendSync(
            $addRequest
                ->setArgument('name', $plan->name_plan)
                ->setArgument('shared-users', (string) $plan->shared_users)
                ->setArgument('rate-limit', $this->rateLimit($plan))
        );
    }

    public function updatePlan(Plan $oldPlan, Plan $newPlan): void
    {
        $client = $this->client($newPlan->routers);

        $printRequest = new Request('/ip/hotspot/user/profile/print');
        $printRequest->setArgument('.proplist', '.id');
        $printRequest->setQuery(Query::where('name', $oldPlan->name_plan));
        $profileId = $client->sendSync($printRequest)->getProperty('.id');

        if (empty($profileId)) {
            $this->addPlan($newPlan);

            return;
        }

        $setRequest = new Request('/ip/hotspot/user/profile/set');
        $client->sendSync(
            $setRequest
                ->setArgument('numbers', $profileId)
                ->setArgument('name', $newPlan->name_plan)
                ->setArgument('shared-users', (string) $newPlan->shared_users)
                ->setArgument('rate-limit', $this->rateLimit($newPlan))
                ->setArgument('on-login', (string) $newPlan->on_login)
                ->setArgument('on-logout', (string) $newPlan->on_logout)
        );
    }

    public function removePlan(Plan $plan): void
    {
        $client = $this->client($plan->routers);

        $printRequest = new Request('/ip/hotspot/user/profile/print');
        $printRequest->setArgument('.proplist', '.id');
        $printRequest->setQuery(Query::where('name', $plan->name_plan));
        $profileId = $client->sendSync($printRequest)->getProperty('.id');

        $removeRequest = new Request('/ip/hotspot/user/profile/remove');
        $client->sendSync($removeRequest->setArgument('numbers', $profileId));
    }

    public function onlineCustomer(Customer $customer, string $routerName): ?string
    {
        $client = $this->client($routerName);

        $printRequest = new Request('/ip/hotspot/active/print');
        $printRequest->setQuery(Query::where('user', $customer->username));

        return $client->sendSync($printRequest)->getProperty('.id');
    }

    public function connectCustomer(Customer $customer, string $ip, string $macAddress, string $routerName): void
    {
        $client = $this->client($routerName);

        $addRequest = new Request('/ip/hotspot/active/login');
        $client->sendSync(
            $addRequest
                ->setArgument('user', $customer->username)
                ->setArgument('password', $customer->password)
                ->setArgument('ip', $ip)
                ->setArgument('mac-address', $macAddress)
        );
    }

    public function disconnectCustomer(Customer $customer, string $routerName): void
    {
        $client = $this->client($routerName);
        $this->removeHotspotActiveUser($client, $customer->username);
    }

    private function client(string $routerName): Client
    {
        $router = Router::where('name', $routerName)->firstOrFail();
        [$host, $port] = array_pad(explode(':', $router->ip_address, 2), 2, null);

        return new Client($host, $router->username, $router->password, $port);
    }

    private function rateLimit(Plan $plan): string
    {
        $bandwidth = Bandwidth::find($plan->id_bw);
        if (! $bandwidth) {
            return '';
        }

        if ($bandwidth->rate_up == '0' || $bandwidth->rate_down == '0') {
            return '';
        }

        $unitDown = $bandwidth->rate_down_unit === 'Kbps' ? 'K' : 'M';
        $unitUp = $bandwidth->rate_up_unit === 'Kbps' ? 'K' : 'M';

        $rate = $bandwidth->rate_up.$unitUp.'/'.$bandwidth->rate_down.$unitDown;
        if (! empty(trim((string) $bandwidth->burst))) {
            $rate .= ' '.$bandwidth->burst;
        }

        return $rate;
    }

    private function removeHotspotUser(Client $client, string $username): void
    {
        $printRequest = new Request('/ip/hotspot/user/print');
        $printRequest->setArgument('.proplist', '.id');
        $printRequest->setQuery(Query::where('name', $username));
        $userId = $client->sendSync($printRequest)->getProperty('.id');

        if (empty($userId)) {
            return;
        }

        $removeRequest = new Request('/ip/hotspot/user/remove');
        $client->sendSync($removeRequest->setArgument('numbers', $userId));
    }

    private function removeHotspotActiveUser(Client $client, string $username): void
    {
        $printRequest = new Request('/ip/hotspot/active/print');
        $printRequest->setArgument('.proplist', '.id');
        $printRequest->setQuery(Query::where('user', $username));
        $id = $client->sendSync($printRequest)->getProperty('.id');

        if (empty($id)) {
            return;
        }

        $removeRequest = new Request('/ip/hotspot/active/remove');
        $client->sendSync($removeRequest->setArgument('numbers', $id));
    }

    private function addHotspotUser(Client $client, Plan $plan, Customer $customer): void
    {
        $addRequest = new Request('/ip/hotspot/user/add');
        $addRequest
            ->setArgument('name', $customer->username)
            ->setArgument('profile', $plan->name_plan)
            ->setArgument('password', $customer->password)
            ->setArgument('comment', $customer->fullname)
            ->setArgument('email', $customer->email);

        if ($plan->typebp === 'Limited') {
            if (in_array($plan->limit_type, ['Time_Limit', 'Both_Limit'], true)) {
                $addRequest->setArgument('limit-uptime', $this->timeLimit($plan));
            }
            if (in_array($plan->limit_type, ['Data_Limit', 'Both_Limit'], true)) {
                $addRequest->setArgument('limit-bytes-total', $this->dataLimit($plan));
            }
        }

        $client->sendSync($addRequest);
    }

    private function timeLimit(Plan $plan): string
    {
        return $plan->time_unit === 'Hrs'
            ? $plan->time_limit.':00:00'
            : '00:'.$plan->time_limit.':00';
    }

    private function dataLimit(Plan $plan): string
    {
        return $plan->data_unit === 'GB'
            ? $plan->data_limit.'000000000'
            : $plan->data_limit.'000000';
    }
}
