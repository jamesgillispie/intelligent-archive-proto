<?php

namespace App\Twill\Capsules\Artworks\Repositories;

use App\Jobs\Indexing\IndexArtwork;
use A17\Twill\Repositories\ModuleRepository;
use App\Twill\Capsules\Artworks\Models\Artwork;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class ArtworkRepository extends ModuleRepository
{
    use HandleSlugs;
    use HandleRevisions;
    use HandleMetadata;

    public function __construct(Artwork $model)
    {
        $this->model = $model;
    }

    /**
     * Editorial changes reach the archive knowledge layer without waiting
     * for a re-import: overrides reindex, unpublishing removes.
     */
    public function afterSave(TwillModelContract $model, array $fields): void
    {
        parent::afterSave($model, $fields);

        IndexArtwork::dispatch((int) $model->getKey());
    }
}
