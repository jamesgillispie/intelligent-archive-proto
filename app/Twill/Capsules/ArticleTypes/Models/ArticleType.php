<?php

namespace App\Twill\Capsules\ArticleTypes\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\HasRevisions;

/**
 * @mixin IdeHelperArticleType
 */
class ArticleType extends Model implements Sortable
{
    use HasSlug;
    use HasRevisions;
    use HasPosition;

    protected $fillable = ['published', 'title', 'position'];

    public array $slugAttributes = ['title'];
}
