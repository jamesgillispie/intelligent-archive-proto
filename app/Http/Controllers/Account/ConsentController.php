<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Memory\ConsentService;
use App\Services\Memory\MemoryDeletionService;

class ConsentController extends Controller
{
    public function __construct(
        private readonly ConsentService $consent,
        private readonly MemoryDeletionService $deletion,
    ) {}

    /**
     * Where the consented middleware sends users whose consent version is
     * stale or revoked: the current agreement, and the choice to renew it.
     */
    public function show(): View
    {
        return view('site.account.consent-required');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->consent->renew($request->user());

        return redirect()
            ->route('account.trails')
            ->with('status', 'Memory agreement renewed. Everything of yours is exactly as you left it.');
    }

    /**
     * Revocation ends the agreement and deletes everything held under it —
     * the confirmation page states both plainly before anything happens.
     */
    public function confirmRevoke(Request $request): View
    {
        return view('site.account.consent-revoke', [
            'counts' => $this->deletion->counts($request->user()),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->consent->revoke($request->user());

        return redirect()
            ->route('account.memory')
            ->with(
                'status',
                'The memory agreement is revoked and everything held under it is deleted. ' .
                    'Memory stays off unless you choose to agree again.',
            );
    }
}
