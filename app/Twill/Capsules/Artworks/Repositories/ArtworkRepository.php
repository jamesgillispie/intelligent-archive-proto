<?php

namespace App\Twill\Capsules\Artworks\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use App\Twill\Capsules\Artworks\Models\Artwork;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class ArtworkRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;
    use HandleMetadata;

    public function __construct(Artwork $model)
    {
        $this->model = $model;
    }
}
