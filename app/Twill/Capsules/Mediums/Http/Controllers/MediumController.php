<?php

namespace App\Twill\Capsules\Mediums\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

/**
 * Imported-only vocabulary; created by the import pipeline, browsable and
 * lightly editable (display name, description) in the CMS.
 */
class MediumController extends BaseModuleController
{
    protected $moduleName = 'mediums';

    protected $titleColumnKey = 'name';

    protected $titleFormKey = 'name';

    protected function setUpController(): void
    {
        $this->disableCreate();
        $this->disableDelete();
        $this->disableBulkDelete();
        $this->disablePermalink();
        $this->enableEditInModal();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->push(
            Input::make()
                ->name('description')
                ->type('textarea')
                ->label('Editorial description')
                ->note('Optional; shown on archive filter pages'),
        );

        return $form;
    }
}
