<?php

namespace App\Twill\Capsules\ContactLandings\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\BladePartial;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Models\Contracts\TwillModelContract;
use App\Twill\Capsules\ContactLandings\Models\ContactLanding;
use App\Twill\Capsules\ContactLandings\Repositories\ContactLandingRepository;
use A17\Twill\Http\Controllers\Admin\SingletonModuleController as BaseModuleController;

class ContactLandingController extends BaseModuleController
{
    protected $moduleName = 'contactLandings';

    protected $previewView = 'site.contact';

    /**
     * This method can be used to enable/disable defaults. See setUpController in the docs for available options.
     */
    protected function setUpController(): void
    {
        $this->disablePermalink();
        $this->disableEditor();
    }

    protected function formData($request): array
    {
        return [
            'publish' => false,
            'editableTitle' => false,
            'customPermalink' => route('contact.index'),
        ];
    }

    /**
     * See the table builder docs for more information. If you remove this method you can use the blade files.
     * When using twill:module:make you can specify --bladeForm to use a blade form instead.
     */
    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->addFieldset(
            Fieldset::make()
                ->title('Content')
                ->fields([Input::make()->name('page_title')->label('Page title')->required()]),
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
        /** @var ContactLanding $item */
        return app(ContactLandingRepository::class)->getShowData($item);
    }
}
