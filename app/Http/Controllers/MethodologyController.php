<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\View\Factory;

/**
 * How memory, consent and suggestions work — stated in code, versioned with
 * the code that makes each statement true, so the page can never promise
 * more than the application delivers (charter: the methodology page records
 * the guarantees; STRATEGY §288 makes "nothing to delete by design" one of
 * them).
 */
class MethodologyController extends Controller
{
    public function __invoke(): Factory|View
    {
        return view('site.methodology', [
            'consentVersion' => config('memory.consent_version'),
        ]);
    }
}
