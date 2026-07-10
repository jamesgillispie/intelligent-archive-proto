<?php

namespace App\Twill\Capsules\AboutLandings\Repositories;

use A17\Twill\Models\Block;
use A17\Twill\Image\TwillImage;
use A17\Twill\Repositories\ModuleRepository;
use App\Services\Video\Video as VideoService;
use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\Twill\Repositories\Behaviors\HandleFieldsGroups;
use App\Twill\Capsules\AboutLandings\Models\AboutLanding;
use App\Twill\Capsules\AboutTimelineItems\Models\AboutTimelineItem;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class AboutLandingRepository extends ModuleRepository
{
    use HandleMedias;
    use HandleRevisions;
    use HandleFieldsGroups;
    use HandleBlocks;
    use HandleMetadata;

    public bool $fieldsGroupsFormFieldNamesAutoPrefix = true;
    public string $fieldsGroupsFormFieldNameSeparator = '.';

    protected array $fieldsGroups = [
        'hero' => ['mission_statement', 'mission_description', 'video_type', 'video_url'],
        'people' => ['team_title'],
    ];

    protected array $repeaters = ['aboutTimelineItems'];

    public function __construct(AboutLanding $model)
    {
        $this->model = $model;
    }

    public function getShowData(AboutLanding $landing): array
    {
        return [
            'title' => $landing->title,
            'hero' => [
                'mission_statement' => $landing->hero['mission_statement'] ?? null,
                'mission_description' => $landing->hero['mission_description'] ?? null,
            ],
            'heroImage' => $landing->hasImage('about-hero-video-image', 'default')
                ? app(TwillImage::class)->make($landing, 'about-hero-video-image')
                : null,
            'heroVideo' => [
                'id' => app(VideoService::class)->getId($landing->hero['video_url'] ?? ''),
                'type' => $landing->hero['video_type'] ?? null,
                'params' => app(VideoService::class)->getEmbedQueryString($landing->hero['video_url'] ?? ''),
                'autoplay' => true,
            ],
            'people' => [
                'title' => nl2br($landing->people['team_title'] ?? ''),
                'groups' => $landing->blocks
                    ->where('type', 'app-peoplegroup')
                    ->map(
                        fn(Block $block) => [
                            'title' => $block->input('title'),
                            'cards' => $block->children
                                ->map(
                                    fn(Block $childBlock) => [
                                        'name' => $childBlock->input('name'),
                                        'title' => $childBlock->input('role'),
                                        'image' => $childBlock->hasImage('person-image', 'default')
                                            ? app(TwillImage::class)->make($childBlock, 'person-image')
                                            : null,
                                    ],
                                )
                                ->toArray(),
                        ],
                    )
                    ->toArray(),
            ],
            'timelineItems' => $landing->aboutTimelineItems
                ->map(
                    fn(AboutTimelineItem $item) => [
                        'year' => $item->year,
                        'title' => $item->title,
                        'description' => $item->description,
                        'image' => $item->hasImage('about-timeline-item', 'default')
                            ? app(TwillImage::class)->make($item, 'about-timeline-item')
                            : null,
                    ],
                )
                ->toArray(),
        ];
    }
}
