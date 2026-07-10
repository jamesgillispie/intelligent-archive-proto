<?php

namespace App\Twill\Capsules\Artworks\Models;

use App\Support\Crops;
use A17\Twill\Models\Model;
use App\Models\Source\SourceRecord;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasMedias;
use App\Models\Behaviors\HasSourceFields;
use Illuminate\Database\Eloquent\Builder;
use A17\Twill\Models\Behaviors\HasRevisions;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use Database\Factories\Archive\ArtworkFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Twill\Capsules\Departments\Models\Department;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use CwsDigital\TwillMetadata\Models\Behaviours\HasMetadata;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * @mixin IdeHelperArtwork
 */
class Artwork extends Model
{
    use HasSlug;
    use HasMedias;
    use HasRevisions;
    use HasMetadata;
    use HasSourceFields;
    /** @use HasFactory<ArtworkFactory> */
    use HasFactory;

    public const IMAGE_ROLE = 'artwork';

    protected $fillable = [
        'published',
        // display fields (editable in Twill; see HasSourceFields)
        'title',
        'display_date',
        'medium_text',
        'dimensions',
        'credit_line',
        'attribution',
        // imported values (written only by the import pipeline)
        'source_title',
        'source_display_date',
        'source_medium_text',
        'source_dimensions',
        'source_credit_line',
        'source_attribution',
        'source_begin_year',
        'source_end_year',
        'source_inscription',
        'source_classification',
        'source_subclassification',
        'source_department_abbr',
        // editorial-only
        'description',
        // identity and canonical relations
        'nga_object_id',
        'accession_number',
        'source_record_id',
        'classification_id',
        'department_id',
        'medium_id',
        'is_displayable',
        'last_synced_at',
    ];

    public array $slugAttributes = ['title'];

    public array $metadataFallbacks = [];

    // Declared so twill-metadata's trait writes a property, not an attribute.
    public array $mediasParams = [];

    /**
     * Downloaded NGA imagery. The single crop is free-ratio and defaults to
     * the full frame: paintings are archival records, never cropped for
     * layout — the frontend letterboxes instead.
     *
     * @return array<string, array<string, array<int, array<string, mixed>>>>
     */
    public function getMediasParams(): array
    {
        return array_merge($this->mediasParams, [
            self::IMAGE_ROLE => Crops::setCrop([
                'name' => Crops::CROP_NAMES['free_ratio'],
                'ratio' => null,
            ]),
        ]);
    }

    /** @var array<int, string> display fields with a source_{field} counterpart */
    public array $sourceMappedFields = [
        'title',
        'display_date',
        'medium_text',
        'dimensions',
        'credit_line',
        'attribution',
    ];

    public $casts = [
        'is_displayable' => 'boolean',
        'source_begin_year' => 'integer',
        'source_end_year' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    protected static function newFactory(): ArtworkFactory
    {
        return ArtworkFactory::new();
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeDisplayable(Builder $query): Builder
    {
        return $query->publishedInListings()->where('is_displayable', true);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function medium(): BelongsTo
    {
        return $this->belongsTo(Medium::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artwork_artist')
            ->withPivot(['role', 'position'])
            ->orderByPivot('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImageAsset::class)->orderByDesc('is_primary')->orderBy('position');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ImageAsset::class)->where('is_primary', true);
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(SourceRecord::class);
    }
}
