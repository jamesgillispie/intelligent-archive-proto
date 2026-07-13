<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Memory\ConsentService;

/**
 * The memory switches. Deliberately outside the consented middleware:
 * resuming must work while paused, and the switches themselves are how a
 * user manages that state.
 */
class MemoryPauseController extends Controller
{
    public function __construct(private readonly ConsentService $consent) {}

    public function pause(Request $request): RedirectResponse
    {
        $this->consent->pause($request->user());

        return redirect()
            ->route('account.memory')
            ->with('status', 'Memory is paused. Nothing new will be kept; nothing already here has changed.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $this->consent->resume($request->user());

        return redirect()->route('account.memory')->with('status', 'Memory is back on.');
    }
}
