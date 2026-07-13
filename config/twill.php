<?php

use App\Support\Crops;
use App\Twill\Capsules\Pages\Repositories\PageRepository;
use App\Twill\Capsules\Articles\Repositories\ArticleRepository;
use App\Twill\Capsules\Homepages\Repositories\HomepageRepository;
use App\Twill\Capsules\AboutLandings\Repositories\AboutLandingRepository;
use App\Twill\Capsules\ContactLandings\Repositories\ContactLandingRepository;

return [
    'admin_middleware_group' => 'admin',

    'capsules' => [
        'list' => [
            [
                'name' => 'Pages',
                'enabled' => true,
            ],
            [
                'name' => 'AboutLandings',
                'enabled' => true,
            ],
            [
                'name' => 'AboutTimelineItems',
                'enabled' => true,
            ],
            [
                'name' => 'ContactLandings',
                'enabled' => true,
            ],
            [
                'name' => 'Articles',
                'enabled' => true,
            ],
            [
                'name' => 'Homepages',
                'enabled' => true,
            ],
            [
                'name' => 'ArticleTypes',
                'enabled' => true,
            ],
            [
                'name' => 'Artworks',
                'enabled' => true,
            ],
            [
                'name' => 'Artists',
                'enabled' => true,
            ],
            [
                'name' => 'Classifications',
                'enabled' => true,
            ],
            [
                'name' => 'Departments',
                'enabled' => true,
            ],
            [
                'name' => 'Mediums',
                'enabled' => true,
            ],
            [
                'name' => 'CuratedSets',
                'enabled' => true,
            ],
        ],
    ],
    'dashboard' => [
        'modules' => [
            'pages' => [
                'name' => 'pages',
                'repository' => PageRepository::class,
                'count' => true,
                'create' => true,
                'search' => true,
                'activity' => true,
            ],
            'articles' => [
                'name' => 'articles',
                'repository' => ArticleRepository::class,
                'count' => true,
                'create' => true,
                'search' => true,
                'activity' => true,
            ],
            'aboutLanding' => [
                'name' => 'aboutLandings',
                'label_singular' => 'About',
                'repository' => AboutLandingRepository::class,
                'count' => false,
                'create' => false,
                'search' => true,
                'activity' => true,
            ],
            'contactLanding' => [
                'name' => 'contactLandings',
                'label_singular' => 'Contact',
                'repository' => ContactLandingRepository::class,
                'count' => false,
                'create' => false,
                'search' => true,
                'activity' => true,
            ],
            'homepages' => [
                'name' => 'homepages',
                'repository' => HomepageRepository::class,
                'count' => false,
                'create' => false,
                'search' => true,
                'activity' => true,
            ],
        ],
    ],
    'block_editor' => [
        'use_twill_blocks' => [],
        'crops' => [
            'block-image' => Crops::setCrop(Crops::SQUARE),
            'block-simple-image' => Crops::setCrop(Crops::_16_9),
            'block-video' => Crops::setCrop(Crops::_16_9),
            'role-block-embed-video' => Crops::setCrop(Crops::_16_9),
            'about-timeline-item' => Crops::setCrop(Crops::_3_2),
            'testimonial-image' => Crops::setCrop(Crops::SQUARE),
            'default_social_image' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 1.91 / 1,
                        'minValues' => [
                            'width' => 1200,
                            'height' => 627,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'wysiwyg' => [
        'default' => [['header' => [3, false]], 'bold', 'italic', 'ordered', 'bullet', 'link', 'clean'],
    ],
];
