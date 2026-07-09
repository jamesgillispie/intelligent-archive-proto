<?php

namespace App\Twill\Capsules\Artists\Models;

use A17\Twill\Models\Model;
use App\Models\Source\SourceRecord;
use A17\Twill\Models\Behaviors\HasSlug;
use App\Models\Behaviors\HasSourceFields;
use A17\Twill\Models\Behaviors\HasRevisions;
use Database\Factories\Archive\ArtistFactory;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperArtist
 */
class Artist extends Model
{
    use HasSlug;
    use HasRevisions;
    use HasSourceFields;
    /** @use HasFactory<ArtistFactory> */
    use HasFactory;

    protected $fillable = [
        'published',
        // display fields (editable in Twill; see HasSourceFields)
        'name',
        'display_date',
        // imported values (written only by the import pipeline)
        'source_name',
        'source_display_date',
        'nationality',
        'begin_year',
        'end_year',
        'ulan_id',
        'wikidata_id',
        'artist_of_nga_object',
        // editorial-only
        'description',
        // identity
        'nga_constituent_id',
        'source_record_id',
        'last_synced_at',
    ];

    public array $slugAttributes = ['name'];

    /** @var array<int, string> display fields with a source_{field} counterpart */
    public array $sourceMappedFields = ['name', 'display_date'];

    public $casts = [
        'begin_year' => 'integer',
        'end_year' => 'integer',
        'artist_of_nga_object' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected static function newFactory(): ArtistFactory
    {
        return ArtistFactory::new();
    }

    public function artworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_artist')->withPivot(['role', 'position']);
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(SourceRecord::class);
    }

    /**
     * Twill listings read `title`; artists use `name`.
     */
    public function getTitleAttribute(): ?string
    {
        return $this->name;
    }
}
