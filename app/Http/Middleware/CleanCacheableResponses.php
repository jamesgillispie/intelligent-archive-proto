<?php

namespace App\Http\Middleware;

use Closure;

class CleanCacheableResponses
{
    /**
     * Cleanup elements of the request if is cacheable
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure  $next
     * @param  string|array  $options
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next, $options = [])
    {
        $response = $next($request);

        // Check if the request was cacheable or not
        if ($response->headers->has('X-Cacheable')) {
            $response->headers->remove('X-Cacheable');
            $response->headers->remove('Set-Cookie');
        }

        return $response;
    }
}
