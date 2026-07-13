<?php

namespace App\Providers;

use A17\Twill\Facades\TwillNavigation;
use Illuminate\Support\Facades\Schema;
use A17\Twill\Facades\TwillAppSettings;
use Illuminate\Support\ServiceProvider;
use A17\Twill\Services\Settings\SettingsGroup;
use A17\Twill\View\Components\Navigation\NavigationLink;

class TwillServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->navigation();
    }

    protected function navigation(): void
    {
        TwillNavigation::clear();

        TwillNavigation::addLink(NavigationLink::make()->forSingleton('homepage'));

        TwillNavigation::addLink(
            NavigationLink::make()
                ->forModule('articles')
                ->title('Articles')
                ->doNotAddSelfAsFirstChild()
                ->setChildren([
                    NavigationLink::make()->forModule('articles'),
                    NavigationLink::make()->forModule('articleTypes')->title('Article types'),
                ]),
        );

        TwillNavigation::addLink(
            NavigationLink::make()
                ->forModule('artworks')
                ->title('Archive')
                ->doNotAddSelfAsFirstChild()
                ->setChildren([
                    NavigationLink::make()->forModule('artworks')->title('Artworks'),
                    NavigationLink::make()->forModule('artists')->title('Artists'),
                    NavigationLink::make()->forModule('classifications')->title('Classifications'),
                    NavigationLink::make()->forModule('departments')->title('Departments'),
                    NavigationLink::make()->forModule('mediums')->title('Mediums'),
                    NavigationLink::make()->forRoute('twill.importBatches.index')->title('Import batches'),
                ]),
        );

        TwillNavigation::addLink(
            NavigationLink::make()
                ->forModule('curatedSets')
                ->title('Curation')
                ->doNotAddSelfAsFirstChild()
                ->setChildren([NavigationLink::make()->forModule('curatedSets')->title('Curated sets')]),
        );

        TwillNavigation::addLink(NavigationLink::make()->forSingleton('aboutLanding')->title('About'));

        TwillNavigation::addLink(NavigationLink::make()->forSingleton('contactLanding')->title('Contact'));

        TwillNavigation::addLink(
            NavigationLink::make()
                ->forModule('pages')
                ->doNotAddSelfAsFirstChild()
                ->setChildren([
                    NavigationLink::make()->forModule('pages'),

                    NavigationLink::make()
                        ->forRoute('twill.app.settings.page', ['group' => 'footer'])
                        ->title('Footer settings'),
                ])
                ->title('Global'),
        );

        TwillAppSettings::registerSettingsGroups(
            SettingsGroup::make()->name('footer')->label('Footer settings')->doNotAutoRegisterMenu(),
        );

        TwillAppSettings::registerSettingsGroups(
            SettingsGroup::make()->name('seo')->label(trans('twill-metadata::form.titles.fieldset')),
        );

        // Prevent twill-metadata from throwing an error is not global Settings has been saved yet
        // (guarded so artisan commands work on a fresh database, before migrations have run)
        if (Schema::hasTable('app_settings')) {
            TwillAppSettings::getGroupForName('seo')->boot();
        }
    }
}
