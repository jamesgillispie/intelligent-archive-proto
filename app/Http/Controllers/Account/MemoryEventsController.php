<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class MemoryEventsController extends Controller
{
    /**
     * The ledger is the user's memory; individual rows are theirs to
     * remove. Deleting a ledger entry is not itself ledgered — the ledger
     * never writes about itself.
     */
    public function destroy(Request $request, int $event): RedirectResponse
    {
        $request->user()->memoryEvents()->findOrFail($event)->delete();

        return redirect()->route('account.memory')->with('status', 'Ledger entry deleted.');
    }
}
