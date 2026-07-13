<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class MemoryActivatedController extends Controller
{
    /**
     * One-time "memory is now on" confirmation shown right after
     * registration; revisits go to the archive.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (!$request->session()->pull('memory.activated', false)) {
            return redirect()->route('archive.index');
        }

        return view('site.account.memory-activated');
    }
}
