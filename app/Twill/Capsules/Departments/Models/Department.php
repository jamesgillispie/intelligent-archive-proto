<?php

namespace App\Twill\Capsules\Departments\Models;

use A17\Twill\Models\Model;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasRevisions;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Imported-only vocabulary derived from NGA object fields. source_name is
 * the immutable match key used by the import pipeline; name is the display
 * value editors may refine.
 *
 * @mixin IdeHelperDepartment
 */
class Department extends Model
{
    protected $table = 'departments';

    use HasSlug;
    use HasRevisions;

    protected $fillable = ['published', 'name', 'source_name', 'description'];

    public array $slugAttributes = ['name'];

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    /**
     * Twill listings read `title`; this vocabulary uses `name`.
     */
    public function getTitleAttribute(): ?string
    {
        return $this->name;
    }
}
