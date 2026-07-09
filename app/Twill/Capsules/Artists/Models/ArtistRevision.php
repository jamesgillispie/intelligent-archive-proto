<?php

namespace App\Twill\Capsules\Artists\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperArtistRevision
 */
class ArtistRevision extends Revision
{
    protected $table = 'artist_revisions';
}
