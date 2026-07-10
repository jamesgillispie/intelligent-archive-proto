<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\SetsMetadata;
use App\Twill\Capsules\Articles\Models\Article;
use App\Twill\Capsules\ArticleTypes\Models\ArticleType;
use App\Twill\Capsules\Articles\Repositories\ArticleRepository;

class ArticlesController extends Controller
{
    use SetsMetadata;

    public function index(Request $request): Factory|View
    {
        $filters = $this->validate(request(), [
            'type' => ['nullable', Rule::exists(ArticleType::class, 'id')],
        ]);

        $articles = Article::query()
            ->publishedInListings()
            ->when(request('type', false), fn($query) => $query->where('article_type_id', $filters['type']))
            ->orderByRaw('coalesce(publication_date, publish_start_date) desc')
            ->paginate(9)
            ->withQueryString();

        $featuredArticle = Article::query()->publishedInListings()->where('featured', true)->first();

        return view('site.articles', [
            'filters' => $this->filters($request),
            'featured' => $featuredArticle ? app(ArticleRepository::class)->getCardData($featuredArticle) : null,
            'items' => $articles
                ->map(fn(Article $article) => app(ArticleRepository::class)->getCardData($article))
                ->toArray(),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'current_page_count' => $articles->firstItem() . ' – ' . $articles->lastItem(),
                'pages' => array_map(
                    fn($url) => [
                        'url' => $url,
                    ],
                    $articles->getUrlRange(1, $articles->lastPage()),
                ),
                'last_page' => $articles->lastPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    protected function filters(Request $request): array
    {
        $filters = [
            [
                'label' => 'All',
                'url' => route('articles.index'),
                'selected' => !request('type'),
            ],
        ];

        Article::select(DB::raw('count(id) as count, article_type_id'))
            ->whereNotNull('article_type_id')
            ->groupBy('article_type_id')
            ->orderBy('count', 'desc')
            ->get()
            ->each(function (Article $article) use (&$filters, $request) {
                $articleType = ArticleType::published()->find($article->article_type_id);

                if (!$articleType) {
                    return;
                }

                $filters[] = [
                    'label' => $articleType->title,
                    'url' => route('articles.index', [
                        'type' => $articleType->id . '-' . Str::slug($articleType->title),
                    ]),
                    'selected' => Str::before($request->input('type'), '-') == $articleType->id,
                ];
            });

        return $filters;
    }

    public function show(string $slug): Factory|View
    {
        $article = Article::query()->publishedInListings()->forSlug($slug)->firstOrFail();

        $this->setMetadata($article);

        return view('site.article', app(ArticleRepository::class)->getShowData($article));
    }
}
