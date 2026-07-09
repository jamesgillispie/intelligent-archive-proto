<?php

namespace App\Twill\Capsules\Classifications\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use App\Twill\Capsules\Classifications\Models\Classification;

class ClassificationRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;

    public function __construct(Classification $model)
    {
        $this->model = $model;
    }
}
