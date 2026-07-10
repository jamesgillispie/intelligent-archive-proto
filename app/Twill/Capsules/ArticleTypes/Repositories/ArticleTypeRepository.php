<?php

namespace App\Twill\Capsules\ArticleTypes\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use App\Twill\Capsules\ArticleTypes\Models\ArticleType;

class ArticleTypeRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;

    public function __construct(ArticleType $model)
    {
        $this->model = $model;
    }
}
