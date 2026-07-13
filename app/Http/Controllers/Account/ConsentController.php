<?php

namespace App\Http\Controllers\Account;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class ConsentController extends Controller
{
    /**
     * Where the consented middleware sends users whose consent version is
     * stale or revoked. The renewal flow itself ships with the memory
     * dashboard (Phase 5); until then this page explains the state.
     */
    public function __invoke(): View
    {
        return view('site.account.consent-required');
    }
}
