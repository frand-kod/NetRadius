<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRadiusRequest
{
    /**
     * Verify that a request to the /api/radius endpoint comes from FreeRADIUS.
     *
     * The shared secret may be supplied either as the `X-Radius-Secret`
     * header or as a `secret` field in the POST body (FreeRADIUS rlm_rest
     * appends it to the `data` payload). Comparison is constant-time.
     *
     * Fail-closed: if the secret is not configured or does not match, the
     * request is rejected with 401.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('radius.secret', '');

        if ($secret === '') {
            return $this->unauthorized('Radius API secret is not configured.');
        }

        $provided = (string) ($request->header('X-Radius-Secret', '') ?: $request->input('secret', ''));

        if (! hash_equals($secret, $provided)) {
            return $this->unauthorized('Invalid Radius API secret.');
        }

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json(['error' => $message], 401);
    }
}
