<?php

namespace App\Twill\Capsules\Articles\Models;

use App\Support\Crops;
use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRelated;
use A17\Twill\Models\Behaviors\HasRevisions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Twill\Capsules\ArticleTypes\Models\ArticleType;
use CwsDigital\TwillMetadata\Models\Behaviours\HasMetadata;

/**
 * @mixin IdeHelperArticle
 */
class Article extends Model
{
    use HasBlocks;
    use HasSlug;
    use HasMedias;
    use HasRevisions;
    use HasRelated;
    use HasMetadata;

    protected $fillable = [
        'published',
        'title',
        'featured',
        'article_type_id',
        'author',
        'publication_date',
        'publish_start_date',
        'publish_end_date',
    ];

    public array $slugAttributes = ['title'];

    public array $metadataFallbacks = [];

    public $casts = [
        'publication_date' => 'date',
        'publish_start_date' => 'datetime',
        'publish_end_date' => 'datetime',
    ];

    public array $mediasParams = [];

    public function getMediasParams(): array
    {
        return array_merge($this->mediasParams ?? [], [
            'cover' => Crops::_4_3,
        ]);
    }

    public function articleType(): BelongsTo
    {
        return $this->belongsTo(ArticleType::class);
    }
}
