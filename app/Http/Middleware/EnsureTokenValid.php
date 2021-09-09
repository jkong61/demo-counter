<?php

namespace App\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;

class EnsureTokenValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() 
            && !is_null($request->get('branch')) 
            && $this->verifyToken($request->bearerToken(), $request->get('branch'))) {
            return $next($request);
        }
        return abort(401, 'Unauthorized action.');
    }

    private function verifyToken(string $token_hash, string $branch_code) : bool {
        $secret = sprintf('fungming-%s-%s', strtoupper($branch_code), CarbonImmutable::now("UTC")->format("Ymd"));
        $hash_string = hash('sha256', $secret);
        return hash_equals($hash_string, $token_hash);
    }
}
