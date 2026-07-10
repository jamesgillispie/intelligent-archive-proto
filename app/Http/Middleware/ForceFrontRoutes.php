<?php

namespace App\Http\Middleware;

use Closure;

class ForceFrontRoutes
{
    /**
     * Add cache related HTTP headers.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next)
    {
        app('url')->forceRootUrl(config('app.url'));

        return $next($request);
    }
}
