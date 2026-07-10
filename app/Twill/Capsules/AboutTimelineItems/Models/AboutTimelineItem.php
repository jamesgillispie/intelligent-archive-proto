<?php

namespace App\Twill\Capsules\AboutTimelineItems\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasPosition;

/**
 * @mixin IdeHelperAboutTimelineItem
 */
class AboutTimelineItem extends Model implements Sortable
{
    use HasMedias;
    use HasPosition;

    protected $fillable = ['title', 'description', 'year', 'position', 'about_landing_id'];
}
