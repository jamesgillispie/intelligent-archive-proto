<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Radios;
use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;

class Link extends TwillBlockComponent
{
    public function render()
    {
        if ($this->input('type') === 'internal') {
            $related = $this->block->getRelated('linked_item')->first();

            $label = $this->input('label') ?? $related->title;
            $href = route('pages.show', $related->slug);
        } else {
            $label = $this->input('label');
            $href = $this->input('url');
        }

        if (empty($label) || empty($href)) {
            return view('site.blocks.invalid-block');
        }

        return view('site.blocks.link', [
            'label' => $label,
            'href' => $href,
        ]);
    }

    public function getForm(): Form
    {
        return Form::make([
            Radios::make()
                ->name('type')
                ->label('Type')
                ->options(
                    Options::fromArray([
                        [
                            'label' => 'Internal',
                            'value' => 'internal',
                        ],
                        [
                            'label' => 'External',
                            'value' => 'external',
                        ],
                    ]),
                )
                ->border()
                ->default('internal'),
            Browser::make()
                ->name('linked_item')
                ->label('Internal page')
                ->modules([
                    [
                        'label' => 'Pages',
                        'name' => 'pages',
                    ],
                ])
                ->connectedTo('type', 'internal', ['keepAlive' => true]),
            Input::make()
                ->name('label')
                ->connectedTo('type', 'internal', ['keepAlive' => true]),
            Columns::make()
                ->left([
                    Input::make()
                        ->name('label')
                        ->connectedTo('type', 'external', ['keepAlive' => true]),
                ])
                ->right([
                    Input::make()
                        ->name('url')
                        ->label('URL')
                        ->connectedTo('type', 'external', ['keepAlive' => true]),
                ]),
        ]);
    }

    public static function getBlockIcon(): string
    {
        return 'b-button';
    }

    public static function getBlockTitleField(): ?string
    {
        return 'label';
    }
}
