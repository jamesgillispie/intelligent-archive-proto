<?php

namespace App\Twill\Capsules\Homepages\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperHomepageRevision
 */
class HomepageRevision extends Revision
{
    protected $table = 'homepage_revisions';
}
