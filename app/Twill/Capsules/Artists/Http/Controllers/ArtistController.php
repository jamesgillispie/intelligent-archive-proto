<?php

namespace App\Twill\Capsules\Artists\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

/**
 * Artists are created by the import pipeline; the CMS is a review/override
 * surface only.
 */
class ArtistController extends BaseModuleController
{
    protected $moduleName = 'artists';

    protected $titleColumnKey = 'name';

    protected $titleFormKey = 'name';

    protected function setUpController(): void
    {
        $this->disableCreate();
        $this->disableDelete();
        $this->disableBulkDelete();
        $this->disableIncludeScheduledInList();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->push(
            Input::make()->name('display_date')->label('Life dates')->note('Editing overrides the imported value'),
            Input::make()->name('nationality')->label('Nationality')->note('Imported'),
            Wysiwyg::make()->name('description')->label('Editorial biography')->note('Editorial field — not imported'),
        );

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->push(
            Text::make()->field('nationality')->title('Nationality'),
            Text::make()->field('display_date')->title('Dates'),
        );

        return $table;
    }
}
