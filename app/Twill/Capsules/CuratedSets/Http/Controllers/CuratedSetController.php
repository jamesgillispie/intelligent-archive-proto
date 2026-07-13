<?php

namespace App\Twill\Capsules\CuratedSets\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Browser;
use App\Twill\Capsules\Artworks\Models\Artwork;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

/**
 * Editorial curation: hand-picked groupings shown on the archive landing.
 */
class CuratedSetController extends BaseModuleController
{
    protected $moduleName = 'curatedSets';

    protected function setUpController(): void
    {
        $this->enableReorder();
        $this->disablePermalink();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->push(
            Input::make()
                ->name('description')
                ->type('textarea')
                ->label('Editorial description')
                ->note('One or two quiet sentences on why these works sit together'),
        );

        $form->push(
            Browser::make()
                ->name('artworks')
                ->label('Works')
                ->modules([Artwork::class])
                ->note('Order here is the order shown on the archive landing'),
        );

        return $form;
    }
}
