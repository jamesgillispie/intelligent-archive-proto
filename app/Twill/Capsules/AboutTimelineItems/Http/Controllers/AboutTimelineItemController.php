<?php

namespace App\Twill\Capsules\AboutTimelineItems\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class AboutTimelineItemController extends BaseModuleController
{
    protected $moduleName = 'aboutTimelineItems';

    protected function setUpController(): void
    {
        $this->disablePermalink();
        $this->enableReorder();
        $this->disablePublish();
        $this->setTitleColumnKey('year');
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->push(
            Input::make()->name('year')->label('Year'),
            Input::make()->name('title'),
            Input::make()->name('description')->type('textarea')->label('Label'),
        );

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(Text::make()->field('label')->title('Label'));

        return $table;
    }
}
