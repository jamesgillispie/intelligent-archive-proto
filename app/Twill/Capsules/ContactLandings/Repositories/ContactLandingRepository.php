<?php

namespace App\Twill\Capsules\ContactLandings\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use App\Twill\Capsules\ContactLandings\Models\ContactLanding;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class ContactLandingRepository extends ModuleRepository
{
    use HandleBlocks;
    use HandleMedias;
    use HandleRevisions;
    use HandleMetadata;

    public function __construct(ContactLanding $model)
    {
        $this->model = $model;
    }

    public function getShowData(ContactLanding $contactLanding):array
    {
        return [
            'title' => $contactLanding->page_title,
        ];
    }
}
