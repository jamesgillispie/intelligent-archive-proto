<?php

namespace App\Tools;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\SitemapIndex;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Twill\Capsules\Articles\Models\Article;

class BuildSitemap
{
    public function build(): void
    {
        $sitemapIndex = SitemapIndex::create()
            ->add($this->buildGenericPages())
            ->add($this->buildIndex(Article::query()->published()->get(), 'sitemap_articles.xml'));

        $this->storeSiteMap('sitemap.xml', $sitemapIndex);
    }

    private function buildIndex(Collection|array $model, string $path): string
    {
        $sitemapRender = Sitemap::create()->add($model);

        $this->storeSiteMap($path, $sitemapRender);

        return $path;
    }

    private function buildGenericPages(): string
    {
        $path = 'sitemap_global_pages.xml';
        $sitemapRender = Sitemap::create()
            // GENERIC PAGES
            ->add(
                Url::create('/')
                    ->setPriority(1)
                    ->setchangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY),
            );

        $this->storeSiteMap($path, $sitemapRender);

        return $path;
    }

    private function storeSiteMap(string $path, Sitemap|SitemapIndex $sitemap): void
    {
        if (config('sitemap.static')) {
            $sitemap->writeToFile(public_path($path));

            return;
        }

        $cacheKey = str_replace('sitemap_', '', $path);
        Cache::store('database')->put(key: $cacheKey, value: $sitemap->render(), ttl: 86400);
    }
}
