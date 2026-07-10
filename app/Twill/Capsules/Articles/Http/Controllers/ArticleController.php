<?php

namespace App\Twill\Capsules\Articles\Http\Controllers;

use App\Support\Blocks;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\BladePartial;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Radios;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Forms\Fields\DatePicker;
use App\Twill\Capsules\Articles\Models\Article;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Models\Contracts\TwillModelContract;
use App\Twill\Capsules\Articles\Repositories\ArticleRepository;
use App\Twill\Capsules\ArticleTypes\Repositories\ArticleTypeRepository;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class ArticleController extends BaseModuleController
{
    protected $moduleName = 'articles';

    protected $previewView = 'site.article';

    protected function setUpController(): void
    {
        $this->enableFeature();
        $this->disableIncludeScheduledInList();
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->push(
            Radios::make()
                ->name('article_type_id')
                ->label('Type')
                ->options(Options::fromArray(app(ArticleTypeRepository::class)->listAll()->toArray()))
                ->columns(2),
            Medias::make()->name('cover'),
            Columns::make()
                ->left([Input::make()->name('author')])
                ->right([DatePicker::make()->name('publication_date')->withoutTime()]),
            BlockEditor::make()->blocks(Blocks::EDITORIAL_BLOCK_NAMES),
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

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->push(
            Text::make()
                ->field('type')
                ->title('Type')
                ->customRender(fn(Article $article) => $article->articleType?->title ?? ''),
        );

        return $table;
    }

    protected function previewData($item)
    {
        /** @var Article $item */
        return app(ArticleRepository::class)->getShowData($item);
    }
}
