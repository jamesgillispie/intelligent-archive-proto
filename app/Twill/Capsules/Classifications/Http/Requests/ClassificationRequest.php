<?php

namespace App\Twill\Capsules\Classifications\Http\Requests;

use A17\Twill\Http\Requests\Admin\Request;

class ClassificationRequest extends Request
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
