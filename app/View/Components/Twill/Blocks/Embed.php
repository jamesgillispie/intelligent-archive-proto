<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Form;
use Illuminate\Contracts\View\View;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class Embed extends TwillBlockComponent
{
    public function render(): View
    {
        $embed = $this->input('embed') ?? null;

        if (empty($embed)) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.embed-block', [
            'caption' => $this->input('caption'),
            'embed' => $this->input('embed'),
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([Input::make()->type('textarea')->name('embed'), Input::make()->name('caption')]);
    }

    public static function getBlockIcon(): string
    {
        return 'b-iframe';
    }
}
