<?php

namespace App\Twill\Capsules\Artworks\Http\Controllers;

use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\BladePartial;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use App\Twill\Capsules\Artworks\Models\Artwork;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

/**
 * CMS surface for canonical artworks. Records are created by the import
 * pipeline, never in the CMS: this controller is the review/override
 * surface. Display fields edited here override imported values (see
 * HasSourceFields); the imported originals stay visible in the form.
 */
class ArtworkController extends BaseModuleController
{
    protected $moduleName = 'artworks';

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
            Input::make()->name('display_date')->label('Display date')->note('Editing overrides the imported value'),
            Input::make()->name('attribution')->label('Attribution')->note('Editing overrides the imported value'),
            Input::make()->name('medium_text')->label('Medium')->note('Editing overrides the imported value'),
            Input::make()->name('dimensions')->label('Dimensions')->note('Editing overrides the imported value'),
            Input::make()->name('credit_line')->label('Credit line')->note('Editing overrides the imported value'),
            Wysiwyg::make()
                ->name('description')
                ->label('Editorial description')
                ->note('Editorial field — not imported, never AI-populated without review'),
        );

        $form->addFieldset(
            Fieldset::make()
                ->title('Imported source values')
                ->id('source')
                ->fields([BladePartial::make()->view('twill.partials.artwork-source-fields')]),
        );

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->push(
            Text::make()->field('attribution')->title('Attribution'),
            Text::make()->field('display_date')->title('Date'),
            Text::make()->field('accession_number')->title('Accession no.'),
            Text::make()
                ->field('is_displayable')
                ->title('Displayable')
                ->customRender(fn(Artwork $artwork) => $artwork->is_displayable ? 'Yes' : 'No'),
        );

        return $table;
    }
}
