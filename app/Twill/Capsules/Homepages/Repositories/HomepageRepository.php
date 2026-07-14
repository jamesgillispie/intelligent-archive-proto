<?php

namespace App\Twill\Capsules\Homepages\Repositories;

use A17\Twill\Image\TwillImage;
use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleFiles;
use App\Twill\Capsules\Homepages\Models\Homepage;
use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleFieldsGroups;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class HomepageRepository extends ModuleRepository
{
    use HandleMedias;
    use HandleFiles;
    use HandleRevisions;
    use HandleFieldsGroups;
    use HandleMetadata;
    use HandleBlocks;

    public bool $fieldsGroupsFormFieldNamesAutoPrefix = true;
    public string $fieldsGroupsFormFieldNameSeparator = '.';

    protected array $fieldsGroups = [
        'about_us' => ['title'],
    ];

    public function __construct(Homepage $model)
    {
        $this->model = $model;
    }

    public function getShowData(Homepage $landing): array
    {
        return [
            'hero' => [
                'tagline' => $landing->tagline,
                'image' => $landing->hasImage('hero-image', '16:9')
                    ? app(TwillImage::class)->make($landing, 'hero-image')
                    : null,
            ],
            'about' => [
                'title' => $landing->about_us['title'] ?? '',
                'image' => $landing->hasImage('about-us-image', '16:9')
                    ? app(TwillImage::class)->make($landing, 'about-us-image')
                    : null,
            ],
        ];
    }
}
