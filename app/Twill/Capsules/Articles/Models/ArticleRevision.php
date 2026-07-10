<?php

namespace App\Twill\Capsules\Articles\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperArticleRevision
 */
class ArticleRevision extends Revision
{
    protected $table = 'article_revisions';
}
