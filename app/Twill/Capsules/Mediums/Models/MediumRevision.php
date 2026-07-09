<?php

namespace App\Twill\Capsules\Mediums\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperMediumRevision
 */
class MediumRevision extends Revision
{
    protected $table = 'medium_revisions';
}
