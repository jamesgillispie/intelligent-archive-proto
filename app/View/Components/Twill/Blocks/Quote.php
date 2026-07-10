<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Form;
use Illuminate\Contracts\View\View;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class Quote extends TwillBlockComponent
{
    public function render(): View
    {
        $quote = $this->input('quote');

        if (empty($quote)) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.blockquote', [
            'quote' => $quote,
            'caption' => $this->input('caption'),
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([Input::make()->name('quote')->type('textarea'), Input::make()->name('caption')]);
    }

    public static function getBlockIcon(): string
    {
        return 'b-quote';
    }
}
