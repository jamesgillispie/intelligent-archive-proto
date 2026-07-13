<?php

namespace App\Twill\Capsules\CuratedSets\Http\Requests;

use A17\Twill\Http\Requests\Admin\Request;

class CuratedSetRequest extends Request
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
