<?php

namespace App\Twill\Capsules\CuratedSets\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\HasRevisions;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A hand-picked artwork grouping — the editorial lever that makes the
 * archive feel curated before any AI is involved (STRATEGY §157). Purely
 * editorial: no source layer, no import provenance, no visitor data.
 */
class CuratedSet extends Model implements Sortable
{
    protected $table = 'curated_sets';

    use HasSlug;
    use HasPosition;
    use HasRevisions;

    protected $fillable = ['published', 'title', 'description', 'position'];

    public array $slugAttributes = ['title'];

    public function artworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_curated_set')
            ->withPivot('position')
            ->orderBy('artwork_curated_set.position');
    }
}
