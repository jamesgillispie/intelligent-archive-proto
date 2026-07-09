<?php

namespace App\Twill\Capsules\Mediums\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use App\Twill\Capsules\Mediums\Models\Medium;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleRevisions;

class MediumRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;

    public function __construct(Medium $model)
    {
        $this->model = $model;
    }
}
