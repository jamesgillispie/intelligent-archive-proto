<?php

namespace App\Twill\Capsules\Homepages\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRelated;
use A17\Twill\Models\Behaviors\HasRevisions;
use CwsDigital\TwillMetadata\Models\Behaviours\HasMetadata;

/**
 * @mixin IdeHelperHomepage
 */
class Homepage extends Model
{
    use HasMedias;
    use HasFiles;
    use HasRevisions;
    use HasRelated;
    use HasMetadata;
    use HasBlocks;

    protected $fillable = ['published', 'title', 'page_title', 'tagline', 'about_us'];

    public $casts = [
        'about_us' => 'array',
    ];

    public array $metadataFallbacks = [];

    public array $filesParams = [];

    public array $mediasParams = [];

    public function getMediasParams(): array
    {
        return array_merge($this->mediasParams ?? [], [
            'hero-image' => [
                '16:9' => [
                    [
                        'name' => '16:9',
                        'ratio' => 16 / 9,
                    ],
                ],
            ],
            'about-us-image' => [
                '16:9' => [
                    [
                        'name' => '16:9',
                        'ratio' => 16 / 9,
                    ],
                ],
            ],
        ]);
    }
}
