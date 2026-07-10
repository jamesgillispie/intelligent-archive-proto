<?php

namespace App\Twill\Capsules\Articles\Repositories;

use Carbon\Carbon;
use A17\Twill\Image\Facades\TwillImage;
use A17\Twill\Repositories\ModuleRepository;
use App\Twill\Capsules\Articles\Models\Article;
use A17\Twill\Repositories\Behaviors\HandleSlugs;
use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\Behaviors\HandleMedias;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use CwsDigital\TwillMetadata\Repositories\Behaviours\HandleMetadata;

class ArticleRepository extends ModuleRepository
{
    use HandleBlocks;
    use HandleSlugs;
    use HandleMedias;
    use HandleRevisions;
    use HandleMetadata;

    public function __construct(Article $model)
    {
        $this->model = $model;
    }

    public function getShowData(Article $article): array
    {
        $latestArticles = Article::query()
            ->publishedInListings()
            ->orderByRaw('coalesce(publication_date, publish_start_date) desc')
            ->take(3)
            ->get();

        return [
            'title' => $article->title,
            'back' => [
                'url' => route('articles.index'),
                'label' => 'All articles',
            ],
            'type' => $article->articleType?->title,
            'published_date' => Carbon::make($article->publication_date ?? $article->publish_start_date)->format(
                'd.m.Y',
            ),
            'author' => $article->author,
            'image' => $article->hasImage('cover') ? TwillImage::make($article, 'cover') : null,
            'page' => $article,
            'newsList' =>
                $latestArticles->count() > 0
                    ? [
                        'title' => 'Latest articles',
                        'button' => [
                            'url' => route('articles.index'),
                            'label' => 'All articles',
                        ],
                        'items' => $latestArticles
                            ->map(fn(Article $article) => $this->getCardData($article))
                            ->toArray(),
                    ]
                    : null,
        ];
    }

    public function getCardData(Article $article): array
    {
        return [
            'title' => $article->title,
            'href' => route('articles.show', $article->slug),
            'category' => $article->articleType?->title,
            'date' => Carbon::make($article->publication_date ?? ($article->publish_start_date ?? now()))->format(
                'd.m.Y',
            ),
            'image' => $article->hasImage('cover') ? TwillImage::make($article, 'cover') : null,
        ];
    }

    public function isUniqueFeature(): bool
    {
        return true;
    }
}
