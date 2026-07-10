<?php

namespace App\Twill\Capsules\Pages\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use CwsDigital\TwillMetadata\Models\Behaviours\HasMetadata;

/**
 * @mixin IdeHelperPage
 */
class Page extends Model
{
    use HasBlocks;
    use HasSlug;
    use HasRevisions;
    use HasMetadata;
    use HasMedias;

    protected $fillable = ['published', 'title', 'description'];

    public array $slugAttributes = ['title'];

    public array $metadataFallbacks = [];

    public array $mediasParams = [];
}
