<?php

namespace App\Twill\Capsules\AboutTimelineItems\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use App\Twill\Capsules\AboutTimelineItems\Models\AboutTimelineItem;

class AboutTimelineItemRepository extends ModuleRepository
{
    use HandleMedias;

    public function __construct(AboutTimelineItem $model)
    {
        $this->model = $model;
    }
}
