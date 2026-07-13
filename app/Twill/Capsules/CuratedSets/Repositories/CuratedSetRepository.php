<?php

namespace App\Twill\Capsules\CuratedSets\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use App\Twill\Capsules\CuratedSets\Models\CuratedSet;

class CuratedSetRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;

    public function __construct(CuratedSet $model)
    {
        $this->model = $model;
    }

    public function afterSave(TwillModelContract $model, array $fields): void
    {
        parent::afterSave($model, $fields);

        $this->updateBrowser($model, $fields, 'artworks');
    }

    public function getFormFields(TwillModelContract $object): array
    {
        $fields = parent::getFormFields($object);

        $fields['browsers']['artworks'] = $this->getFormFieldsForBrowser($object, 'artworks');

        return $fields;
    }
}
