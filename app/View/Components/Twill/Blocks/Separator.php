<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Form;
use Illuminate\Contracts\View\View;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class Separator extends TwillBlockComponent
{
    public function render(): View
    {
        return view('site.blocks.separator');
    }

    public function getForm(): Form
    {
        return Form::make([]);
    }
}
