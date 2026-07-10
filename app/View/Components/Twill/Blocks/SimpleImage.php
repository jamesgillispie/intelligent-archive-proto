<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Image\TwillImage;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use App\Support\Crops;

class SimpleImage extends TwillBlockComponent
{
    public function render()
    {

        if (!$this->block->hasImage('block-simple-image')) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.image-block', [
            'caption' => $this->block->imageCaption('block-simple-image'),
            'media' => app(TwillImage::class)->make($this->block, 'block-simple-image'),
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([Medias::make()->name('block-simple-image')->label('Image')->max(1)]);
    }

    public static function getBlockIcon(): string
    {
        return 'b-image';
    }

    public static function getBlockTitle(): string
    {
        return 'Image';
    }
}
