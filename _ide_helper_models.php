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


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

namespace App\Twill\Capsules\AboutLandings\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $published
 * @property string|null $title
 * @property string|null $page_title
 * @property array|null $hero
 * @property array|null $people
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\AboutTimelineItems\Models\AboutTimelineItem> $aboutTimelineItems
 * @property-read int|null $about_timeline_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
 * @property-read int|null $blocks_count
 * @property-read string|null $social_image
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
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding draft()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding mine()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding published()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding query()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding visible()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereHero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding wherePeople($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLanding withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAboutLanding {}
}

namespace App\Twill\Capsules\AboutLandings\Models{
/**
 * 
 *
 * @property int $id
 * @property int $about_landing_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payload
 * @property-read mixed $by_user
 * @property-read \A17\Twill\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision query()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision whereAboutLandingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutLandingRevision whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAboutLandingRevision {}
}

namespace App\Twill\Capsules\AboutTimelineItems\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem draft()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem published()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem visible()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereAboutLandingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AboutTimelineItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAboutTimelineItem {}
}

namespace App\Twill\Capsules\ArticleTypes\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType draft()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType forFallbackLocaleSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType forInactiveSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType forSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType mine()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType published()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType visible()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleType withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticleType {}
}

namespace App\Twill\Capsules\ArticleTypes\Models{
/**
 * 
 *
 * @property int $id
 * @property int $article_type_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payload
 * @property-read mixed $by_user
 * @property-read \A17\Twill\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision query()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision whereArticleTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeRevision whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticleTypeRevision {}
}

namespace App\Twill\Capsules\ArticleTypes\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug draft()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug published()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug query()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug visible()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereArticleTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleTypeSlug withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticleTypeSlug {}
}

namespace App\Twill\Capsules\Articles\Models{
/**
 * 
 *
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
 * @property-read string|null $social_image
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
 * @method static \Illuminate\Database\Eloquent\Builder|Article accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Article draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Article forFallbackLocaleSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Article forInactiveSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Article forSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Article mine()
 * @method static \Illuminate\Database\Eloquent\Builder|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Article onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Article published()
 * @method static \Illuminate\Database\Eloquent\Builder|Article publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|Article query()
 * @method static \Illuminate\Database\Eloquent\Builder|Article visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereArticleTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article wherePublicationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article wherePublishEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article wherePublishStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Article withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Article withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Article withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Article withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticle {}
}

namespace App\Twill\Capsules\Articles\Models{
/**
 * 
 *
 * @property int $id
 * @property int $article_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payload
 * @property-read mixed $by_user
 * @property-read \A17\Twill\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision query()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleRevision whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticleRevision {}
}

namespace App\Twill\Capsules\Articles\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug draft()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug published()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug query()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug visible()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereArticleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ArticleSlug withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticleSlug {}
}

namespace App\Twill\Capsules\ContactLandings\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $published
 * @property string|null $title
 * @property string|null $page_title
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
 * @property-read int|null $blocks_count
 * @property-read string|null $social_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
 * @property-read int|null $medias_count
 * @property-read \CwsDigital\TwillMetadata\Models\Metadata|null $metadata
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Twill\Capsules\ContactLandings\Models\ContactLandingRevision> $revisions
 * @property-read int|null $revisions_count
 * @property-write mixed $publish_start_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding draft()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding mine()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding published()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding visible()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLanding withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperContactLanding {}
}

namespace App\Twill\Capsules\ContactLandings\Models{
/**
 * 
 *
 * @property int $id
 * @property int $contact_landing_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payload
 * @property-read mixed $by_user
 * @property-read \A17\Twill\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision whereContactLandingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactLandingRevision whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperContactLandingRevision {}
}

namespace App\Twill\Capsules\Homepages\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $published
 * @property string|null $title
 * @property string|null $page_title
 * @property string|null $tagline
 * @property array|null $about_us
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Block> $blocks
 * @property-read int|null $blocks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\File> $files
 * @property-read int|null $files_count
 * @property-read string|null $social_image
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
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage mine()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage published()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage query()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereAboutUs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Homepage withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperHomepage {}
}

namespace App\Twill\Capsules\Homepages\Models{
/**
 * 
 *
 * @property int $id
 * @property int $homepage_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payload
 * @property-read mixed $by_user
 * @property-read \A17\Twill\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision query()
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision whereHomepageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HomepageRevision whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperHomepageRevision {}
}

namespace App\Twill\Capsules\Pages\Models{
/**
 * 
 *
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
 * @property-read string|null $social_image
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
 * @method static \Illuminate\Database\Eloquent\Builder|Page accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Page draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Page forFallbackLocaleSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Page forInactiveSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Page forSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Page mine()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Page published()
 * @method static \Illuminate\Database\Eloquent\Builder|Page publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Page withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Page withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Page withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPage {}
}

namespace App\Twill\Capsules\Pages\Models{
/**
 * 
 *
 * @property int $id
 * @property int $page_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $payload
 * @property-read mixed $by_user
 * @property-read \A17\Twill\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision query()
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageRevision whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPageRevision {}
}

namespace App\Twill\Capsules\Pages\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug draft()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug published()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug query()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug visible()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|PageSlug withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPageSlug {}
}

namespace A17\Twill\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting draft()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting published()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting visible()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAppSetting {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $blockable_id
 * @property string|null $blockable_type
 * @property int $position
 * @property array $content
 * @property string $type
 * @property string|null $child_key
 * @property int|null $parent_id
 * @property string|null $editor_name
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $blockable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Block> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\File> $files
 * @property-read int|null $files_count
 * @property-read mixed $presenter
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
 * @property-read int|null $medias_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\RelatedItem> $relatedItems
 * @property-read int|null $related_items_count
 * @method static \Illuminate\Database\Eloquent\Builder|Block accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Block draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Block editor($name = 'default')
 * @method static \Illuminate\Database\Eloquent\Builder|Block newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Block onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Block published()
 * @method static \Illuminate\Database\Eloquent\Builder|Block query()
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereBlockableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereBlockableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereChildKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereEditorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Block whereType($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBlock {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property int $id
 * @property string $featured_id
 * @property string $featured_type
 * @property string $bucket_key
 * @property int $position
 * @property bool $starred
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $featured
 * @method static \Illuminate\Database\Eloquent\Builder|Feature forBucket(string $bucketKey)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereBucketKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereFeaturedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereFeaturedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereStarred($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperFeature {}
}

namespace A17\Twill\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|File accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|File draft()
 * @method static \Illuminate\Database\Eloquent\Builder|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|File onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|File published()
 * @method static \Illuminate\Database\Eloquent\Builder|File publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|File query()
 * @method static \Illuminate\Database\Eloquent\Builder|File unused()
 * @method static \Illuminate\Database\Eloquent\Builder|File visible()
 * @method static \Illuminate\Database\Eloquent\Builder|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|File withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|File withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|File withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|File withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperFile {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property-read bool $can_edit
 * @property-read bool $can_publish
 * @property-read string $created_at
 * @property-read string $title_in_browser
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\User> $users
 * @method static \Illuminate\Database\Eloquent\Builder|Group accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Group draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group published()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGroup {}
}

namespace A17\Twill\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|Media accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Media draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Media published()
 * @method static \Illuminate\Database\Eloquent\Builder|Media publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder|Media unused()
 * @method static \Illuminate\Database\Eloquent\Builder|Media visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Media withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Media withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Media withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMedia {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property-read string $permissionable_module
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $permissionable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Permission global()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission module()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission moduleItem()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission ofItem(\Illuminate\Database\Eloquent\Model $item)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission ofModel(string $model)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission ofModuleName(string $moduleName)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPermission {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $subject_id
 * @property string $subject_type
 * @property int|null $related_id
 * @property string $related_type
 * @property string $browser_name
 * @property int $position
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $related
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $subject
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem whereBrowserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem whereRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem whereRelatedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RelatedItem whereSubjectType($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRelatedItem {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property-read \Illuminate\Database\Eloquent\Collection $accessible_roles
 * @property-read string $created_at
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Media> $medias
 * @property-read int|null $medias_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\User> $users
 * @method static \Illuminate\Database\Eloquent\Builder|Role accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Role draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Role ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|Role published()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Role withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRole {}
}

namespace A17\Twill\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|Setting accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting draft()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting orderByRawByTranslation($orderRawString, $groupByField, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting orderByTranslation($orderField, $orderType = 'ASC', $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting published()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting translated()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withActiveTranslations(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|Setting withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSetting {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property int $id
 * @property string $namespace
 * @property string $slug
 * @property string $name
 * @property int $count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $taggable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \A17\Twill\Models\Tagged> $tagged
 * @property-read int|null $tagged_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag name(string $name)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag slug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereNamespace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereSlug($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTag {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @property int $id
 * @property string $taggable_type
 * @property int $taggable_id
 * @property int $tag_id
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged whereTaggableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tagged whereTaggableType($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTagged {}
}

namespace A17\Twill\Models\Translations{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation draft()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation published()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation publishedInListings()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation visible()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereTwillSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation withTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation withoutTag($tags, string $type = 'slug')
 * @method static \Illuminate\Database\Eloquent\Builder|SettingTranslation withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSettingTranslation {}
}

namespace A17\Twill\Models{
/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|User accessible()
 * @method static \Illuminate\Database\Eloquent\Builder|User activated()
 * @method static \Illuminate\Database\Eloquent\Builder|User draft()
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User notSuperAdmin()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User pending()
 * @method static \Illuminate\Database\Eloquent\Builder|User published()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRegisteredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRequireNewPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

namespace A17\Twill\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserOauth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserOauth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserOauth query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUserOauth {}
}

