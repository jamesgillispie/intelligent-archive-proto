<?php

namespace App\Twill\Capsules\Pages\Http\Controllers;

use App\Support\Blocks;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fieldset;
use App\Twill\Capsules\Pages\Models\Page;
use A17\Twill\Services\Forms\BladePartial;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class PageController extends BaseModuleController
{
    protected $moduleName = 'pages';

    protected $permalinkBase = '';

    protected $previewView = 'site.page';

    protected function setUpController(): void {}

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->push(
            Input::make()->name('description')->label('Description')->type('textarea'),
            BlockEditor::make()->blocks(Blocks::ALL_BLOCKS),
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
        /** @var Page $item */
        if (method_exists($this->repository, 'getShowData')) {
            return $this->repository->getShowData($item);
        }

        return [];
    }
}
