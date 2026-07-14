<?php

namespace App\Services\Trails;

use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Starting points drawn from a consented user's own trails — artist,
 * medium and classification names from works they explicitly saved. Only
 * the seeds are personal: explore results for any query stay identical
 * for every visitor, and deriving seeds never writes context anywhere.
 * Consent gates the reading of trails; pause does not — pause stops
 * writes, never reads of the user's own record.
 */
class TrailSeedService
{
    public function __construct(private readonly ConsentService $consent) {}

    /**
     * @return list<string>
     */
    public function seedsFor(?User $user, string $excludingQuery = '', int $limit = 6): array
    {
        if ($user === null || !$this->consent->hasCurrentConsent($user)) {
            return [];
        }

        $artworkIds = TrailItem::whereIn('research_trail_id', $user->trails()->active()->select('id'))
            ->pluck('artwork_id')
            ->unique();

        if ($artworkIds->isEmpty()) {
            return [];
        }

        $artworks = Artwork::displayable()
            ->with(['artists', 'classification', 'medium'])
            ->findMany($artworkIds);

        return $artworks
            ->flatMap(
                fn(Artwork $artwork) => [
                    ...$artwork->artists->pluck('name')->all(),
                    $artwork->classification?->name,
                    $artwork->medium?->name,
                ],
            )
            ->filter()
            ->map(fn($seed) => (string) $seed)
            ->countBy()
            ->sortKeys()
            ->sortDesc()
            ->keys()
            ->reject(fn(string $seed) => mb_strtolower($seed) === mb_strtolower($excludingQuery))
            ->take($limit)
            ->values()
            ->all();
    }
}
