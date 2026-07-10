<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimpleCacheHeader
{
    /**
     * Add cache related HTTP headers.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|array $options
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next, string|array $options = []): Response
    {
        $response = $next($request);

        if (!$request->isMethodCacheable() || (!$response->getContent() && !$response instanceof BinaryFileResponse)) {
            return $response;
        }

        // Set X-Cacheable header so CleanCacheableResponses can strip cookies
        // from the response.
        $response->header('X-Cacheable', 'true');

        if (is_string($options)) {
            $options = $this->parseOptions($options);
        }

        $response->setCache($options);

        return $response;
    }

    /**
     * Parse the given header options.
     *
     * @param  string  $options
     * @return array
     */
    protected function parseOptions(string $options): array
    {
        return collect(explode(';', rtrim($options, ';')))
            ->mapWithKeys(function ($option) {
                $data = explode('=', $option, 2);

                return [$data[0] => $data[1] ?? true];
            })
            ->all();
    }
}
