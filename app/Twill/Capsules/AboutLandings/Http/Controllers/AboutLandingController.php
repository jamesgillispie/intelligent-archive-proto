<?php

namespace App\Twill\Capsules\AboutLandings\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\BladePartial;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Radios;
use A17\Twill\Services\Forms\InlineRepeater;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Models\Contracts\TwillModelContract;
use App\Twill\Capsules\AboutLandings\Models\AboutLanding;
use App\Twill\Capsules\AboutLandings\Repositories\AboutLandingRepository;
use A17\Twill\Http\Controllers\Admin\SingletonModuleController as BaseModuleController;

class AboutLandingController extends BaseModuleController
{
    protected $moduleName = 'aboutLandings';

    protected $previewView = 'site.about';

    protected function setUpController(): void
    {
        $this->disablePermalink();
    }

    protected function formData($request): array
    {
        return [
            'publish' => false,
            'editableTitle' => false,
            'customPermalink' => route('about.index'),
        ];
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->addFieldset(
            Fieldset::make()
                ->title('Hero')
                ->fields([
                    Input::make()
                        ->name('hero.mission_statement')
                        ->label('Mission statement')
                        ->type('textarea')
                        ->rows(2),
                    Input::make()
                        ->name('hero.mission_description')
                        ->label('Mission description')
                        ->type('textarea')
                        ->rows(2),
                    Radios::make()
                        ->name('hero.video_type')
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
                            ]),
                        )
                        ->label('Video type')
                        ->default('youtube')
                        ->columns(2),
                    Input::make()->name('hero.video_url')->label('Video URL'),
                    Medias::make()->name('about-hero-video-image')->label('Cover image')->max(1),
                ]),
        );

        $form->addFieldset(
            Fieldset::make()
                ->title('People')
                ->fields([
                    Input::make()->name('people.team_title')->label('Team title')->type('textarea')->rows(2),
                    BlockEditor::make()
                        ->blocks(['app-peoplegroup'])
                        ->label('Add a people section'),
                ]),
        );

        $form->addFieldset(
            Fieldset::make()
                ->title('Timeline')
                ->fields([
                    InlineRepeater::make()
                        ->name('aboutTimelineItems')
                        ->label('Timeline year')
                        ->triggerText('Add a timeline year')
                        ->fields([
                            Input::make()->name('year'),
                            Input::make()->name('title')->type('textarea'),
                            Input::make()->name('description')->type('textarea'),
                            Medias::make()->name('about-timeline-item'),
                        ]),
                ]),
        );

        $form->addFieldset(
            Fieldset::make()
                ->title('SEO')
                ->closed()
                ->fields([
                    BladePartial::make()
                        ->view('twill-metadata::includes.metadata-fields')
                        ->withAdditionalParams([
                            'metadata_card_type_options' => config('metadata.card_type_options'),
                            'metadata_og_type_options' => config('metadata.opengraph_type_options'),
                        ]),
                ]),
        );

        return $form;
    }

    protected function previewData($item)
    {
        /** @var AboutLanding $item */
        return app(AboutLandingRepository::class)->getShowData($item);
    }
}
