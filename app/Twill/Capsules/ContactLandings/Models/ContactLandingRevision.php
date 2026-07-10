<?php

namespace App\Twill\Capsules\ContactLandings\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperContactLandingRevision
 */
class ContactLandingRevision extends Revision
{
    protected $table = 'contact_landing_revisions';
}
