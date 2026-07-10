<?php

namespace App\Twill\Capsules\Pages\Repositories;

use App\Twill\Capsules\Pages\Models\Page;
use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class PageRepository extends ModuleRepository
{
    use HandleBlocks;
    use HandleSlugs;
    use HandleRevisions;
    use HandleMetadata;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }

    public function getShowData(Page $page): array
    {
        return [
            'title' => $page->title,
            'page' => $page,
        ];
    }
}
