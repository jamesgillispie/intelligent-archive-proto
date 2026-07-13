<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */

namespace App\Models\Memory {
    /**
     * The consent record login creates. Authoritative gate every memory write
     * checks; export includes it; revocation triggers the deletion flow.
     *
     * @property int $id
     * @property int $user_id
     * @property string $consent_version
     * @property \Illuminate\Support\Carbon $granted_at
     * @property \Illuminate\Support\Carbon|null $revoked_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User $user
     * @method static \Database\Factories\Memory\MemoryConsentFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereConsentVersion($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereGrantedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereRevokedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MemoryConsent whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperMemoryConsent {}
}

namespace App\Models\Memory {
    /**
     * The user's memory switches. Pause != delete: pausing stops writes while
     * the durable SQL record stays intact and readable.
     *
     * @property int $id
     * @property int $user_id
     * @property bool $memory_paused
     * @property bool $semantic_suggestions_enabled
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\User $user
     * @method static \Database\Factories\Memory\PersonalizationSettingFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting whereMemoryPaused($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting whereSemanticSuggestionsEnabled($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalizationSetting whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPersonalizationSetting {}
}

namespace App\Models\Source {
    /**
     * Typed view over source_records rows from published_images.csv.
     *
     * Single-table: shares the source_records table with a global scope; exists
     * for ergonomic access to IIIF/rights fields of the raw image rows.
     *
     * @property int $id
     * @property int $source_import_id
     * @property string $source_table
     * @property string $source_id
     * @property array<array-key, mixed> $payload
     * @property string $content_hash
     * @property int $version
     * @property string|null $rights
     * @property \Illuminate\Support\Carbon|null $source_modified_at
     * @property \Illuminate\Support\Carbon|null $first_seen_at
     * @property \Illuminate\Support\Carbon|null $last_seen_at
     * @property \Illuminate\Support\Carbon|null $missing_from_source_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Source\SourceImport $import
     * @method static \Database\Factories\Source\SourceRecordFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage forTable(string $sourceTable)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage presentInSource()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereContentHash($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereFirstSeenAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereLastSeenAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereMissingFromSourceAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereRights($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereSourceId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereSourceImportId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereSourceModifiedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereSourceTable($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImage whereVersion($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperSourceImage {}
}

namespace App\Models\Source {
    /**
     * One NGA Open Data import run — the provenance anchor for every source
     * record and, transitively, every canonical archive record.
     *
     * @property int $id
     * @property string|null $snapshot_ref
     * @property string $data_path
     * @property string $status
     * @property array<array-key, mixed>|null $report
     * @property \Illuminate\Support\Carbon|null $started_at
     * @property \Illuminate\Support\Carbon|null $finished_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Source\SourceRecord> $records
     * @property-read int|null $records_count
     * @method static \Database\Factories\Source\SourceImportFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereDataPath($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereFinishedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereReport($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereSnapshotRef($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereStartedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceImport whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperSourceImport {}
}

namespace App\Models\Source {
    /**
     * One raw NGA Open Data row, preserved verbatim in `payload`.
     *
     * Immutable by convention: only the import pipeline writes here (updates are
     * limited to re-import bookkeeping: payload refresh + version bump on
     * upstream change, last_seen_at, missing_from_source_at). No Twill module
     * manages this layer, and canonical models never edit it.
     *
     * @property int $id
     * @property int $source_import_id
     * @property string $source_table
     * @property string $source_id
     * @property array<array-key, mixed> $payload
     * @property string $content_hash
     * @property int $version
     * @property string|null $rights
     * @property \Illuminate\Support\Carbon|null $source_modified_at
     * @property \Illuminate\Support\Carbon|null $first_seen_at
     * @property \Illuminate\Support\Carbon|null $last_seen_at
     * @property \Illuminate\Support\Carbon|null $missing_from_source_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Source\SourceImport $import
     * @method static \Database\Factories\Source\SourceRecordFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord forTable(string $sourceTable)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord presentInSource()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereContentHash($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereFirstSeenAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereLastSeenAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereMissingFromSourceAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereRights($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereSourceId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereSourceImportId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereSourceModifiedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereSourceTable($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SourceRecord whereVersion($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperSourceRecord {}
}

namespace App\Models {
    /**
     * @property int $id
     * @property string $name
     * @property string $email
     * @property string|null $email_verified_at
     * @property string $password
     * @property string|null $remember_token
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Memory\MemoryConsent|null $memoryConsent
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Memory\MemoryConsent> $memoryConsents
     * @property-read int|null $memory_consents_count
     * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
     * @property-read int|null $notifications_count
     * @property-read \App\Models\Memory\PersonalizationSetting|null $personalizationSetting
     * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperUser {}
}

namespace App\Twill\Capsules\AboutLandings\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $title
     * @property string|null $page_title
     * @property array<array-key, mixed>|null $hero
     * @property array<array-key, mixed>|null $people
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\AboutTimelineItems\Models\AboutTimelineItem> $aboutTimelineItems
     * @property-read int|null $about_timeline_items_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
     * @property-read int|null $blocks_count
     * @property-read string $meta_description
     * @property-read mixed $social_image
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\RelatedItem> $relatedItems
     * @property-read int|null $related_items_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\AboutLandings\Models\AboutLandingRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereHero($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding wherePageTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding wherePeople($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLanding withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperAboutLanding {}
}

namespace App\Twill\Capsules\AboutLandings\Models {
    /**
     * @property int $id
     * @property int $about_landing_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision whereAboutLandingId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutLandingRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperAboutLandingRevision {}
}

namespace App\Twill\Capsules\AboutTimelineItems\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $year
     * @property int|null $position
     * @property int $about_landing_id
     * @property string|null $title
     * @property string|null $description
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem ordered()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereAboutLandingId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem wherePosition($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem whereYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutTimelineItem withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperAboutTimelineItem {}
}

namespace App\Twill\Capsules\ArticleTypes\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $title
     * @property int|null $position
     * @property string $slug
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\ArticleTypes\Models\ArticleTypeRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\ArticleTypes\Models\ArticleTypeSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType ordered()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType wherePosition($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleType withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArticleType {}
}

namespace App\Twill\Capsules\ArticleTypes\Models {
    /**
     * @property int $id
     * @property int $article_type_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision whereArticleTypeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArticleTypeRevision {}
}

namespace App\Twill\Capsules\ArticleTypes\Models {
    /**
     * @property int $id
     * @property int $article_type_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereArticleTypeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleTypeSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArticleTypeSlug {}
}

namespace App\Twill\Capsules\Articles\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $title
     * @property \Illuminate\Support\Carbon|null $publish_start_date
     * @property \Illuminate\Support\Carbon|null $publish_end_date
     * @property string|null $author
     * @property \Illuminate\Support\Carbon|null $publication_date
     * @property int|null $article_type_id
     * @property bool $featured
     * @property-read \App\Twill\Capsules\ArticleTypes\Models\ArticleType|null $articleType
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
     * @property-read int|null $blocks_count
     * @property string $slug
     * @property-read mixed $social_image
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\RelatedItem> $relatedItems
     * @property-read int|null $related_items_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Articles\Models\ArticleRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Articles\Models\ArticleSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereArticleTypeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereAuthor($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereFeatured($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article wherePublicationDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article wherePublishEndDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article wherePublishStartDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArticle {}
}

namespace App\Twill\Capsules\Articles\Models {
    /**
     * @property int $id
     * @property int $article_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision whereArticleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArticleRevision {}
}

namespace App\Twill\Capsules\Articles\Models {
    /**
     * @property int $id
     * @property int $article_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereArticleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArticleSlug {}
}

namespace App\Twill\Capsules\Artists\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string $nga_constituent_id
     * @property int|null $source_record_id
     * @property \Illuminate\Support\Carbon|null $last_synced_at
     * @property string|null $name
     * @property string|null $display_date
     * @property string|null $source_name
     * @property string|null $source_display_date
     * @property string|null $nationality
     * @property int|null $begin_year
     * @property int|null $end_year
     * @property string|null $ulan_id
     * @property string|null $wikidata_id
     * @property bool $artist_of_nga_object
     * @property string|null $description
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\Artwork> $artworks
     * @property-read int|null $artworks_count
     * @property string $slug
     * @property-read string|null $title
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artists\Models\ArtistRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artists\Models\ArtistSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \App\Models\Source\SourceRecord|null $sourceRecord
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist draft()
     * @method static \Database\Factories\Archive\ArtistFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereArtistOfNgaObject($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereBeginYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereDisplayDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereEndYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereLastSyncedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereNationality($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereNgaConstituentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereSourceDisplayDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereSourceName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereSourceRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereUlanId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereWikidataId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArtist {}
}

namespace App\Twill\Capsules\Artists\Models {
    /**
     * @property int $id
     * @property int $artist_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision whereArtistId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArtistRevision {}
}

namespace App\Twill\Capsules\Artists\Models {
    /**
     * @property int $id
     * @property int $artist_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereArtistId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtistSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArtistSlug {}
}

namespace App\Twill\Capsules\Artworks\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string $nga_object_id
     * @property string|null $accession_number
     * @property int|null $source_record_id
     * @property \Illuminate\Support\Carbon|null $last_synced_at
     * @property string|null $title
     * @property string|null $display_date
     * @property string|null $medium_text
     * @property string|null $dimensions
     * @property string|null $credit_line
     * @property string|null $attribution
     * @property string|null $source_title
     * @property string|null $source_display_date
     * @property string|null $source_medium_text
     * @property string|null $source_dimensions
     * @property string|null $source_credit_line
     * @property string|null $source_attribution
     * @property int|null $source_begin_year
     * @property int|null $source_end_year
     * @property string|null $source_inscription
     * @property string|null $source_classification
     * @property string|null $source_subclassification
     * @property string|null $source_department_abbr
     * @property string|null $description
     * @property int|null $classification_id
     * @property int|null $department_id
     * @property int|null $medium_id
     * @property bool $is_displayable
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artists\Models\Artist> $artists
     * @property-read int|null $artists_count
     * @property-read \App\Twill\Capsules\Classifications\Models\Classification|null $classification
     * @property-read \App\Twill\Capsules\Departments\Models\Department|null $department
     * @property string $slug
     * @property-read mixed $social_image
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\ImageAsset> $images
     * @property-read int|null $images_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \App\Twill\Capsules\Mediums\Models\Medium|null $medium
     * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
     * @property-read \App\Twill\Capsules\Artworks\Models\ImageAsset|null $primaryImage
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\ArtworkRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\ArtworkSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \App\Models\Source\SourceRecord|null $sourceRecord
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork displayable()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork draft()
     * @method static \Database\Factories\Archive\ArtworkFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereAccessionNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereAttribution($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereClassificationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereCreditLine($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereDepartmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereDimensions($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereDisplayDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereIsDisplayable($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereLastSyncedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereMediumId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereMediumText($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereNgaObjectId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceAttribution($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceBeginYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceClassification($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceCreditLine($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceDepartmentAbbr($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceDimensions($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceDisplayDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceEndYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceInscription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceMediumText($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceSubclassification($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereSourceTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Artwork withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArtwork {}
}

namespace App\Twill\Capsules\Artworks\Models {
    /**
     * @property int $id
     * @property int $artwork_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision whereArtworkId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArtworkRevision {}
}

namespace App\Twill\Capsules\Artworks\Models {
    /**
     * @property int $id
     * @property int $artwork_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereArtworkId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ArtworkSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperArtworkSlug {}
}

namespace App\Twill\Capsules\Artworks\Models {
    /**
     * A published NGA IIIF image for an artwork. The IIIF metadata (base URL,
     * native dimensions, rights cap) is the durable record — any size can be
     * re-derived from it without re-scanning the dataset. A downloaded copy is
     * attached to the artwork as Twill media (`media_id`); the download is a
     * cache of the IIIF source, never a replacement for it.
     *
     * @property int $id
     * @property int $artwork_id
     * @property string $nga_uuid
     * @property string|null $iiif_url
     * @property string|null $iiif_thumb_url
     * @property string|null $view_type
     * @property bool $is_primary
     * @property int|null $width
     * @property int|null $height
     * @property int|null $max_pixels
     * @property string|null $assistive_text
     * @property string $validation_status
     * @property \Illuminate\Support\Carbon|null $validated_at
     * @property int $position
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property int|null $media_id
     * @property \Illuminate\Support\Carbon|null $downloaded_at
     * @property string|null $failure_reason
     * @property-read \App\Twill\Capsules\Artworks\Models\Artwork $artwork
     * @property-read \A17\Twill\Models\Media|null $media
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereArtworkId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereAssistiveText($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereDownloadedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereFailureReason($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereHeight($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereIiifThumbUrl($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereIiifUrl($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereIsPrimary($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereMaxPixels($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereMediaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereNgaUuid($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset wherePosition($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereValidatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereValidationStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereViewType($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageAsset whereWidth($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperImageAsset {}
}

namespace App\Twill\Capsules\Classifications\Models {
    /**
     * Imported-only vocabulary derived from NGA object fields. source_name is
     * the immutable match key used by the import pipeline; name is the display
     * value editors may refine.
     *
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $name
     * @property string $source_name
     * @property string|null $description
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\Artwork> $artworks
     * @property-read int|null $artworks_count
     * @property string $slug
     * @property-read string|null $title
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Classifications\Models\ClassificationRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Classifications\Models\ClassificationSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereSourceName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Classification withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperClassification {}
}

namespace App\Twill\Capsules\Classifications\Models {
    /**
     * @property int $id
     * @property int $classification_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision whereClassificationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperClassificationRevision {}
}

namespace App\Twill\Capsules\Classifications\Models {
    /**
     * @property int $id
     * @property int $classification_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereClassificationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ClassificationSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperClassificationSlug {}
}

namespace App\Twill\Capsules\ContactLandings\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $title
     * @property string|null $page_title
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
     * @property-read int|null $blocks_count
     * @property-read mixed $social_image
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\ContactLandings\Models\ContactLandingRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding wherePageTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLanding withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperContactLanding {}
}

namespace App\Twill\Capsules\ContactLandings\Models {
    /**
     * @property int $id
     * @property int $contact_landing_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision whereContactLandingId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactLandingRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperContactLandingRevision {}
}

namespace App\Twill\Capsules\Departments\Models {
    /**
     * Imported-only vocabulary derived from NGA object fields. source_name is
     * the immutable match key used by the import pipeline; name is the display
     * value editors may refine.
     *
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $name
     * @property string $source_name
     * @property string|null $description
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\Artwork> $artworks
     * @property-read int|null $artworks_count
     * @property string $slug
     * @property-read string|null $title
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Departments\Models\DepartmentRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Departments\Models\DepartmentSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereSourceName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperDepartment {}
}

namespace App\Twill\Capsules\Departments\Models {
    /**
     * @property int $id
     * @property int $department_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision whereDepartmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperDepartmentRevision {}
}

namespace App\Twill\Capsules\Departments\Models {
    /**
     * @property int $id
     * @property int $department_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereDepartmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|DepartmentSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperDepartmentSlug {}
}

namespace App\Twill\Capsules\Homepages\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $title
     * @property string|null $page_title
     * @property string|null $tagline
     * @property array<array-key, mixed>|null $about_us
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
     * @property-read int|null $blocks_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\File> $files
     * @property-read int|null $files_count
     * @property-read mixed $social_image
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\RelatedItem> $relatedItems
     * @property-read int|null $related_items_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Homepages\Models\HomepageRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereAboutUs($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage wherePageTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereTagline($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Homepage withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperHomepage {}
}

namespace App\Twill\Capsules\Homepages\Models {
    /**
     * @property int $id
     * @property int $homepage_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision whereHomepageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|HomepageRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperHomepageRevision {}
}

namespace App\Twill\Capsules\Mediums\Models {
    /**
     * Imported-only vocabulary derived from NGA object fields. source_name is
     * the immutable match key used by the import pipeline; name is the display
     * value editors may refine.
     *
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $name
     * @property string $source_name
     * @property string|null $description
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Artworks\Models\Artwork> $artworks
     * @property-read int|null $artworks_count
     * @property string $slug
     * @property-read string|null $title
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Mediums\Models\MediumRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Mediums\Models\MediumSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereSourceName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Medium withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperMedium {}
}

namespace App\Twill\Capsules\Mediums\Models {
    /**
     * @property int $id
     * @property int $medium_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision whereMediumId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperMediumRevision {}
}

namespace App\Twill\Capsules\Mediums\Models {
    /**
     * @property int $id
     * @property int $medium_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereMediumId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|MediumSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperMediumSlug {}
}

namespace App\Twill\Capsules\Pages\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $title
     * @property string|null $description
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
     * @property-read int|null $blocks_count
     * @property string $slug
     * @property-read mixed $social_image
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Pages\Models\PageRevision> $revisions
     * @property-read int|null $revisions_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\Pages\Models\PageSlug> $slugs
     * @property-read int|null $slugs_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page forFallbackLocaleSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page forInactiveSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page forSlug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page mine()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePublished($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPage {}
}

namespace App\Twill\Capsules\Pages\Models {
    /**
     * @property int $id
     * @property int $page_id
     * @property int|null $user_id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $payload
     * @property-read mixed $by_user
     * @property-read \A17\Twill\Models\User|null $user
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision wherePageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision wherePayload($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageRevision whereUserId($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPageRevision {}
}

namespace App\Twill\Capsules\Pages\Models {
    /**
     * @property int $id
     * @property int $page_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $slug
     * @property string $locale
     * @property bool $active
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug accessible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug published()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug wherePageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug withTrashed(bool $withTrashed = true)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSlug withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPageSlug {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string|null $name
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
     * @property-read int|null $blocks_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static Builder<static>|AppSetting accessible()
     * @method static Builder<static>|AppSetting draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting newQuery()
     * @method static Builder<static>|AppSetting onlyTrashed()
     * @method static Builder<static>|AppSetting published()
     * @method static Builder<static>|AppSetting publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting query()
     * @method static Builder<static>|AppSetting visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting wherePublished($value)
     * @method static Builder<static>|AppSetting whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting whereUpdatedAt($value)
     * @method static Builder<static>|AppSetting withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|AppSetting withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|AppSetting withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperAppSetting {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property int|null $blockable_id
     * @property string|null $blockable_type
     * @property int $position
     * @property array<array-key, mixed> $content
     * @property string $type
     * @property string|null $child_key
     * @property int|null $parent_id
     * @property string|null $editor_name
     * @property-read BaseModel|\Eloquent|null $blockable
     * @property-read \Illuminate\Database\Eloquent\Collection<int, Block> $children
     * @property-read int|null $children_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\File> $files
     * @property-read int|null $files_count
     * @property-read mixed $presenter
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\RelatedItem> $relatedItems
     * @property-read int|null $related_items_count
     * @method static Builder<static>|Block accessible()
     * @method static Builder<static>|Block draft()
     * @method static Builder<static>|Block editor($name = 'default')
     * @method static Builder<static>|Block newModelQuery()
     * @method static Builder<static>|Block newQuery()
     * @method static Builder<static>|Block onlyTrashed()
     * @method static Builder<static>|Block published()
     * @method static Builder<static>|Block query()
     * @method static Builder<static>|Block whereBlockableId($value)
     * @method static Builder<static>|Block whereBlockableType($value)
     * @method static Builder<static>|Block whereChildKey($value)
     * @method static Builder<static>|Block whereContent($value)
     * @method static Builder<static>|Block whereEditorName($value)
     * @method static Builder<static>|Block whereId($value)
     * @method static Builder<static>|Block whereParentId($value)
     * @method static Builder<static>|Block wherePosition($value)
     * @method static Builder<static>|Block whereType($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperBlock {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property string $featured_id
     * @property string $featured_type
     * @property string $bucket_key
     * @property int $position
     * @property bool $starred
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read BaseModel|\Eloquent $featured
     * @method static Builder<static>|Feature forBucket(string $bucketKey)
     * @method static Builder<static>|Feature newModelQuery()
     * @method static Builder<static>|Feature newQuery()
     * @method static Builder<static>|Feature query()
     * @method static Builder<static>|Feature whereBucketKey($value)
     * @method static Builder<static>|Feature whereCreatedAt($value)
     * @method static Builder<static>|Feature whereFeaturedId($value)
     * @method static Builder<static>|Feature whereFeaturedType($value)
     * @method static Builder<static>|Feature whereId($value)
     * @method static Builder<static>|Feature wherePosition($value)
     * @method static Builder<static>|Feature whereStarred($value)
     * @method static Builder<static>|Feature whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperFeature {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property string $uuid
     * @property string|null $filename
     * @property int $size
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static Builder<static>|File accessible()
     * @method static Builder<static>|File draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
     * @method static Builder<static>|File onlyTrashed()
     * @method static Builder<static>|File published()
     * @method static Builder<static>|File publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File unused()
     * @method static Builder<static>|File visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFilename($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereSize($value)
     * @method static Builder<static>|File whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUuid($value)
     * @method static Builder<static>|File withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|File withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|File withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperFile {}
}

namespace A17\Twill\Models {
    /**
     * @property-read bool $can_edit
     * @property-read bool $can_publish
     * @property-read string $created_at
     * @property-read string $title_in_browser
     * @property-read int|null $users_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Permission> $permissions
     * @property-read int|null $permissions_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\User> $users
     * @method static Builder<static>|Group accessible()
     * @method static Builder<static>|Group draft()
     * @method static Builder<static>|Group newModelQuery()
     * @method static Builder<static>|Group newQuery()
     * @method static Builder<static>|Group onlyTrashed()
     * @method static Builder<static>|Group published()
     * @method static Builder<static>|Group query()
     * @method static Builder<static>|Group withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|Group withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperGroup {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property string $uuid
     * @property string|null $alt_text
     * @property int $width
     * @property int $height
     * @property string|null $caption
     * @property string|null $filename
     * @property-read mixed $dimensions
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static Builder<static>|Media accessible()
     * @method static Builder<static>|Media draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
     * @method static Builder<static>|Media onlyTrashed()
     * @method static Builder<static>|Media published()
     * @method static Builder<static>|Media publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media unused()
     * @method static Builder<static>|Media visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereAltText($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCaption($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFilename($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereHeight($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
     * @method static Builder<static>|Media whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUuid($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereWidth($value)
     * @method static Builder<static>|Media withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|Media withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Media withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperMedia {}
}

namespace A17\Twill\Models {
    /**
     * @property-read string $permissionable_module
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Group> $groups
     * @property-read int|null $groups_count
     * @property-read BaseModel|\Eloquent $permissionable
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Role> $roles
     * @property-read int|null $roles_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\User> $users
     * @property-read int|null $users_count
     * @method static Builder<static>|Permission global()
     * @method static Builder<static>|Permission module()
     * @method static Builder<static>|Permission moduleItem()
     * @method static Builder<static>|Permission newModelQuery()
     * @method static Builder<static>|Permission newQuery()
     * @method static Builder<static>|Permission ofItem(\Illuminate\Database\Eloquent\Model $item)
     * @method static Builder<static>|Permission ofModel(string $model)
     * @method static Builder<static>|Permission ofModuleName(string $moduleName)
     * @method static Builder<static>|Permission query()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPermission {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property int|null $subject_id
     * @property string $subject_type
     * @property int|null $related_id
     * @property string $related_type
     * @property string $browser_name
     * @property int $position
     * @property-read BaseModel|\Eloquent|null $related
     * @property-read BaseModel|\Eloquent|null $subject
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem whereBrowserName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem wherePosition($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem whereRelatedId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem whereRelatedType($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem whereSubjectId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|RelatedItem whereSubjectType($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperRelatedItem {}
}

namespace A17\Twill\Models {
    /**
     * @property-read Collection $accessible_roles
     * @property-read string $created_at
     * @property-read int|null $users_count
     * @property-read Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read Collection<int, \A17\Twill\Models\Permission> $permissions
     * @property-read int|null $permissions_count
     * @property-read Collection<int, \A17\Twill\Models\User> $users
     * @method static Builder<static>|Role accessible()
     * @method static Builder<static>|Role draft()
     * @method static Builder<static>|Role newModelQuery()
     * @method static Builder<static>|Role newQuery()
     * @method static Builder<static>|Role onlyTrashed()
     * @method static Builder<static>|Role ordered()
     * @method static Builder<static>|Role published()
     * @method static Builder<static>|Role query()
     * @method static Builder<static>|Role withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|Role withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperRole {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property string|null $key
     * @property string|null $section
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @property-read \A17\Twill\Models\Translations\SettingTranslation|null $translation
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Translations\SettingTranslation> $translations
     * @property-read int|null $translations_count
     * @property string $value
     * @property string $locale
     * @property string $active
     * @method static Builder<static>|Setting accessible()
     * @method static Builder<static>|Setting draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting listsTranslations(string $translationField)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting notTranslatedIn(?string $locale = null)
     * @method static Builder<static>|Setting onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting orWhereTranslation(string $translationField, $value, ?string $locale = null)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting orderByRawByTranslation($orderRawString, $groupByField, $locale = null)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting orderByTranslation($orderField, $orderType = 'ASC', $locale = null)
     * @method static Builder<static>|Setting published()
     * @method static Builder<static>|Setting publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting translated()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting translatedIn(?string $locale = null)
     * @method static Builder<static>|Setting visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSection($value)
     * @method static Builder<static>|Setting whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTranslationLike(string $translationField, $value, ?string $locale = null)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting withActiveTranslations(?string $locale = null)
     * @method static Builder<static>|Setting withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting withTranslation(?string $locale = null)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|Setting withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperSetting {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property string $namespace
     * @property string $slug
     * @property string $name
     * @property int $count
     * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $taggable
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tagged> $tagged
     * @property-read int|null $tagged_count
     * @method static Builder<static>|Tag name(string $name)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag query()
     * @method static Builder<static>|Tag slug(string $slug)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereCount($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereNamespace($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereSlug($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperTag {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property string $taggable_type
     * @property int $taggable_id
     * @property int $tag_id
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged whereTagId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged whereTaggableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tagged whereTaggableType($value)
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperTagged {}
}

namespace A17\Twill\Models\Translations {
    /**
     * @property int $id
     * @property int $twill_setting_id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string $locale
     * @property bool $active
     * @property string|null $value
     * @property-write mixed $publish_start_date
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
     * @property-read int|null $tags_count
     * @method static Builder<static>|SettingTranslation accessible()
     * @method static Builder<static>|SettingTranslation draft()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation newQuery()
     * @method static Builder<static>|SettingTranslation onlyTrashed()
     * @method static Builder<static>|SettingTranslation published()
     * @method static Builder<static>|SettingTranslation publishedInListings()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation query()
     * @method static Builder<static>|SettingTranslation visible()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereLocale($value)
     * @method static Builder<static>|SettingTranslation whereTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereTwillSettingId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation whereValue($value)
     * @method static Builder<static>|SettingTranslation withTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|SettingTranslation withoutTag($tags, string $type = 'slug')
     * @method static \Illuminate\Database\Eloquent\Builder<static>|SettingTranslation withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperSettingTranslation {}
}

namespace A17\Twill\Models {
    /**
     * @property int $id
     * @property \Illuminate\Support\Carbon|null $deleted_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property bool $published
     * @property string $name
     * @property string $email
     * @property string|null $password
     * @property \A17\Twill\Models\Role|null $role
     * @property string|null $title
     * @property string|null $description
     * @property string|null $remember_token
     * @property string|null $language
     * @property \Illuminate\Support\Carbon|null $last_login_at
     * @property \Illuminate\Support\Carbon|null $registered_at
     * @property bool $require_new_password
     * @property-read mixed $can_delete
     * @property mixed $google2fa_secret
     * @property-read mixed $last_login_column_value
     * @property-read mixed $role_value
     * @property-read mixed $title_in_browser
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Group> $groups
     * @property-read int|null $groups_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
     * @property-read int|null $medias_count
     * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
     * @property-read int|null $notifications_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Permission> $permissions
     * @property-read int|null $permissions_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\UserOauth> $providers
     * @property-read int|null $providers_count
     * @method static Builder<static>|User accessible()
     * @method static Builder<static>|User activated()
     * @method static Builder<static>|User draft()
     * @method static Builder<static>|User newModelQuery()
     * @method static Builder<static>|User newQuery()
     * @method static Builder<static>|User notSuperAdmin()
     * @method static Builder<static>|User onlyTrashed()
     * @method static Builder<static>|User pending()
     * @method static Builder<static>|User published()
     * @method static Builder<static>|User query()
     * @method static Builder<static>|User whereCreatedAt($value)
     * @method static Builder<static>|User whereDeletedAt($value)
     * @method static Builder<static>|User whereDescription($value)
     * @method static Builder<static>|User whereEmail($value)
     * @method static Builder<static>|User whereId($value)
     * @method static Builder<static>|User whereLanguage($value)
     * @method static Builder<static>|User whereLastLoginAt($value)
     * @method static Builder<static>|User whereName($value)
     * @method static Builder<static>|User wherePassword($value)
     * @method static Builder<static>|User wherePublished($value)
     * @method static Builder<static>|User whereRegisteredAt($value)
     * @method static Builder<static>|User whereRememberToken($value)
     * @method static Builder<static>|User whereRequireNewPassword($value)
     * @method static Builder<static>|User whereRole($value)
     * @method static Builder<static>|User whereTitle($value)
     * @method static Builder<static>|User whereUpdatedAt($value)
     * @method static Builder<static>|User withTrashed(bool $withTrashed = true)
     * @method static Builder<static>|User withoutTrashed()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperUser {}
}

namespace A17\Twill\Models {
    /**
     * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOauth newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOauth newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|UserOauth query()
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperUserOauth {}
}
