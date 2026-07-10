<?php

namespace App\Twill\Capsules\ArticleTypes\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperArticleTypeRevision
 */
class ArticleTypeRevision extends Revision
{
    protected $table = 'article_type_revisions';
}
