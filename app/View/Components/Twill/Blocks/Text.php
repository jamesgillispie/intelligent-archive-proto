<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Form;
use Illuminate\Contracts\View\View;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class Text extends TwillBlockComponent
{
    public function render(): View
    {
        $text = $this->input('text') ?? null;

        if (empty($text)) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.text-block', [
            'text' => $text,
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([
            Wysiwyg::make()->name('text')->limitHeight()->toolbarOptions(config('twill.wysiwyg.default')),
        ]);
    }
}
