<?php

namespace App\Http\Controllers\Account;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;

class TrailsController extends Controller
{
    /**
     * Phase 3 stub: the consented landing place. Trail building itself is
     * Phase 4 scope.
     */
    public function __invoke(): View
    {
        return view('site.account.trails');
    }
}
