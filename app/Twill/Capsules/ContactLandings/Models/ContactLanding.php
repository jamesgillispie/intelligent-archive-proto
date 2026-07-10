<?php

namespace App\Twill\Capsules\ContactLandings\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use CwsDigital\TwillMetadata\Models\Behaviours\HasMetadata;

/**
 * @mixin IdeHelperContactLanding
 */
class ContactLanding extends Model
{
    use HasBlocks;
    use HasMedias;
    use HasRevisions;
    use HasMetadata;

    protected $fillable = ['published', 'title', 'page_title'];

    public array $metadataFallbacks = [];

    public array $mediasParams = [];
}
