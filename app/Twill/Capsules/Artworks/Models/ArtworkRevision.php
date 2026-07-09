<?php

namespace App\Twill\Capsules\Artworks\Models;

use A17\Twill\Models\Revision;

/**
 * @mixin IdeHelperArtworkRevision
 */
class ArtworkRevision extends Revision
{
    protected $table = 'artwork_revisions';
}
