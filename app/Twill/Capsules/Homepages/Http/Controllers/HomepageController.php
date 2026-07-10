<?php

namespace App\Twill\Capsules\Homepages\Http\Controllers;

use App\Support\Blocks;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\BladePartial;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use App\Twill\Capsules\Homepages\Models\Homepage;
use A17\Twill\Models\Contracts\TwillModelContract;
use App\Twill\Capsules\Homepages\Repositories\HomepageRepository;
use A17\Twill\Http\Controllers\Admin\SingletonModuleController as BaseModuleController;

class HomepageController extends BaseModuleController
{
    protected $moduleName = 'homepages';

    protected $previewView = 'site.homepage';

    protected function setUpController(): void
    {
        $this->disablePermalink();
    }

    protected function formData($request): array
    {
        return [
            'publish' => false,
            'editableTitle' => false,
            'customPermalink' => route('home'),
        ];
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->addFieldset(
            Fieldset::make()
                ->title('Hero')
                ->fields([
                    Input::make()->name('tagline')->label('Tagline')->type('textarea')->rows(2),
                    Medias::make()->name('hero-image')->label('Image'),
                    BlockEditor::make()->blocks(Blocks::LANDING_BLOCK_NAMES),
                ]),
        );

        $form->addFieldset(
            Fieldset::make()
                ->title('About us')
                ->fields([
                    Input::make()->name('about_us.title')->label('Title')->type('textarea')->rows(2),
                    Medias::make()->name('about-us-image')->label('Image'),
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
        /** @var Homepage $item */
        return app(HomepageRepository::class)->getShowData($item);
    }
}
