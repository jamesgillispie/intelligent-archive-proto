<?php

namespace App\Twill\Capsules\Artists\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use App\Twill\Capsules\Artists\Models\Artist;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleRevisions;

class ArtistRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;

    public function __construct(Artist $model)
    {
        $this->model = $model;
    }
}
