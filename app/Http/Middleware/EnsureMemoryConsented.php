<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Memory\ConsentService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards memory routes: requires a current-version consent record and
 * honors pause — paused memory blocks writes but never reads, because
 * the user can always inspect what the archive remembers.
 */
class EnsureMemoryConsented
{
    public function __construct(private readonly ConsentService $consent) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if (!$this->consent->hasCurrentConsent($user)) {
            return redirect()->route('account.consent');
        }

        if ($this->consent->isPaused($user) && !$request->isMethodSafe()) {
            abort(403, 'Memory is paused. Resume memory to save or change anything.');
        }

        return $next($request);
    }
}
