<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Image\TwillImage;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Radios;
use App\Services\Video\Video as VideoService;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class SimpleVideo extends TwillBlockComponent
{
    public function render()
    {
        $type = $this->input('type');


        if ($type === 'other') {
            return view('site.blocks.embed-block', [
                'caption' => $this->input('caption'),
                'embed' => $this->input('embed'),
            ]);
        }


        if (empty($this->input('video_url'))) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.video-block', [
            'media' => app(TwillImage::class)->make($this->block, 'block-video'),
            'video' => [
                'id' => app(VideoService::class)->getId($this->input('video_url')),
                'type' => $this->input('type'),
                'params' => app(VideoService::class)->getEmbedQueryString($this->input('video_url')),
                'autoplay' => true,
            ],
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([
            Radios::make()
                ->name('type')
                ->options(
                    Options::fromArray([
                        [
                            'value' => 'youtube',
                            'label' => 'YouTube',
                        ],
                        [
                            'value' => 'vimeo',
                            'label' => 'Vimeo',
                        ],
                        [
                            'value' => 'other',
                            'label' => 'Other',
                        ],
                    ]),
                )
                ->default('youtube')
                ->columns(3),
            Input::make()
                ->name('video_url')
                ->label('Video URL')
                ->connectedTo('type', ['youtube', 'vimeo'], ['keepAlive' => true]),
            Medias::make()
                ->name('block-video')
                ->label('Cover image')
                ->max(1)
                ->connectedTo('type', ['youtube', 'vimeo'], ['keepAlive' => true]),
            Input::make()
                ->name('embed')
                ->label('Embed code')
                ->type('textarea')
                ->rows(5)
                ->connectedTo('type', 'other', ['keepAlive' => true]),
        ]);
    }

    public static function getBlockIcon(): string
    {
        return 'b-video';
    }

    public static function getBlockTitle(): string
    {
        return 'Video';
    }
}
