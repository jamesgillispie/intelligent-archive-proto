<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Image\TwillImage;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class Image extends TwillBlockComponent
{
    public function render()
    {
        if (!$this->block->hasImage('block-image')) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.image-block', [
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'caption' => $this->block->imageCaption('block-image'),
            'media' => app(TwillImage::class)->make($this->block, 'block-image'),

            'button' => [
                'label' => $this->input('url_label'),
                'url' => $this->input('url'),
                'type' => 'button',
            ],
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([
            Input::make()->name('title'),
            Input::make()->name('description')->type('textarea'),
            Medias::make()->name('block-image')->label('Image')->max(2),
            Columns::make()
                ->left([Input::make()->name('url')->label('Link URL')])
                ->right([Input::make()->name('url_label')->label('Link URL label')]),
        ]);
    }

    public static function getBlockIcon(): string
    {
        return 'b-image';
    }

    public static function getBlockTitle(): string
    {
        return 'Image';
    }

    public static function getBlockTitleField(): ?string
    {
        return 'title';
    }
}
