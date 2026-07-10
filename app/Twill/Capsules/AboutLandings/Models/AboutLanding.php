<?php

namespace App\Twill\Capsules\AboutLandings\Models;

use App\Support\Crops;
use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRelated;
use A17\Twill\Models\Behaviors\HasRevisions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use CwsDigital\TwillMetadata\Models\Behaviours\HasMetadata;
use App\Twill\Capsules\AboutTimelineItems\Models\AboutTimelineItem;

/**
 * @mixin IdeHelperAboutLanding
 */
class AboutLanding extends Model
{
    use HasMedias;
    use HasRevisions;
    use HasRelated;
    use HasBlocks;
    use HasMetadata;

    protected $fillable = ['published', 'title', 'page_title', 'hero', 'people'];

    protected $casts = [
        'hero' => 'array',
        'people' => 'array',
    ];

    public array $metadataFallbacks = [
        'description' => 'metaDescription',
    ];

    public array $mediasParams = [];

    public function getMediasParams(): array
    {
        return array_merge($this->mediasParams ?? [], [
            'about-hero-video-image' => [
                Crops::setCrop(Crops::_16_9) + Crops::setCrop(Crops::SQUARE, Crops::CROP_NAMES['mobile']),
            ],
        ]);
    }

    public function aboutTimelineItems(): HasMany
    {
        return $this->hasMany(AboutTimelineItem::class)->orderBy('position');
    }

    public function getMetaDescriptionAttribute(): string
    {
        return $this->hero['mission_statement'] ?? '';
    }
}
