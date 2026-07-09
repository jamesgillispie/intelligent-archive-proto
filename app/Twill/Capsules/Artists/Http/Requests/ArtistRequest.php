<?php

namespace App\Twill\Capsules\Artists\Http\Requests;

use A17\Twill\Http\Requests\Admin\Request;

class ArtistRequest extends Request
{
    public function rulesForCreate()
    {
        return [];
    }

    public function rulesForUpdate()
    {
        return [];
    }
}
