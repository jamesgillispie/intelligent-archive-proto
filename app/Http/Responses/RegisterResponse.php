<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Registration just turned memory on; land on the one-time confirmation
     * before the archive. The session flag is what makes it one-time.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request)
    {
        $request->session()->put('memory.activated', true);

        return $request->wantsJson() ? new JsonResponse('', 201) : redirect()->route('account.memory-activated');
    }
}
