<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Memory\ConsentService;

class ConsentController extends Controller
{
    public function __construct(private readonly ConsentService $consent) {}

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
}
