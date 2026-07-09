<?php

namespace App\Twill\Capsules\Classifications\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperClassificationRevision
 */
class ClassificationRevision extends Revision
{
    protected $table = 'classification_revisions';
}
