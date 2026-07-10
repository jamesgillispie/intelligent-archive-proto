<?php

namespace App\Providers;

use A17\Twill\Models\Block;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use A17\Twill\Facades\TwillAppSettings;
use Illuminate\Support\ServiceProvider;
use App\Twill\Capsules\Pages\Models\Page;
use App\Twill\Capsules\Articles\Models\Article;
use App\Twill\Capsules\Homepages\Models\Homepage;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Twill\Capsules\ArticleTypes\Models\ArticleType;
use App\Twill\Capsules\AboutLandings\Models\AboutLanding;
use App\Twill\Capsules\ContactLandings\Models\ContactLanding;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'homepages' => Homepage::class,
            'pages' => Page::class,
            'aboutLanding' => AboutLanding::class,
            'contactLanding' => ContactLanding::class,
            'articles' => Article::class,
            'articleTypes' => ArticleType::class,
        ]);

        $this->bootBladeSharedVariables();

        $this->bootBladeComponents();

        $this->bootViewCreators();
    }

    /**
     * Define shared variables through all blade files
     * @return void
     */
    protected function bootBladeSharedVariables(): void
    {
        View::share('FE', config('frontend'));
        View::share('BP', config('frontend')['structure']['breakpointsFull'] ?? []);
    }

    /**
     * Define frontend components path with aliases
     * @return void
     */
    protected function bootBladeComponents(): void
    {
        Blade::anonymousComponentNamespace('layouts', 'layouts');
        Blade::anonymousComponentNamespace('partials', 'partials');
        Blade::anonymousComponentNamespace('forms', 'forms');
        Blade::anonymousComponentNamespace('site/blocks', 'blocks');
        Blade::anonymousComponentNamespace('twill/partials/form', 'twill-partials-form');
    }

    protected function bootViewCreators(): void
    {
        View::creator('components.navigation.footer', function (\Illuminate\View\View $view) {
            try {
                $footerAddressBlock = TwillAppSettings::getGroupDataForSectionAndName('footer', 'address');

                $view->with(
                    'offices',
                    $footerAddressBlock->children->map(function (Block $block) {
                        return [
                            'title' => $block->input('title'),
                            'address' => [$block->input('line1'), $block->input('line2')],
                        ];
                    }),
                );
            } catch (\Exception) {
                return;
            }
        });
    }
}
