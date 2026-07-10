# %project-name%

> CMS and web application made with Twill 3

## Tech stack
- [Laravel 11](https://laravel.com/docs/11.x)
- [Twill 3](https://twillcms.com/)
- [Blast](https://github.com/area17/blast)
- [Twill Image](https://github.com/area17/twill-image)
- [Vitrine UI Blade](https://code.area17.com/a17/vitrine/vitrine-ui-blade/-/tree/raw-components?ref_type=heads)
- [A17 Behaviors](https://github.com/area17/a17-behaviors)
- [A17 Helpers](https://github.com/area17/js-helpers)
- [Tailwind](https://tailwindcss.com/) + [A17 Tailwind Plugins](https://area17.github.io/tailwind-plugins/)

## Development environment

### Laravel Valet

```shell
$ git clone git@code.area17.com:%slug-project-name%/website.git
$ cd %slug-project-name%
$ cp .env.example .env
$ composer install
$ php artisan key:generate
$ php artisan migrate
$ php artisan twill:superadmin
$ valet link %slug-project-name%
```

Twill admin should now be visible at [http://project-name.test/admin](http://project-name.test/admin).

## Backend architecture

### Meta tags
Meta tags are defined with [butschster/LaravelMetaTags](https://github.com/butschster/LaravelMetaTags) package.
Default configuration is defined in ``config/meta-tags.php``.
Trait ``SetsMetadata`` is used to define meta tags for each model. Fallback rules are defined for title, description and image.

### Sitemap
Sitemap is generated with [Spatie/laravel-sitemap](https://github.com/spatie/laravel-sitemap) package.
You can define sitemap rules in ``Tools\BuildSitemap`` and generate sitemap with ``php artisan app:generate-sitemap`` command.

By setting `SITEMAP_STATIC` to `true` in `.env` file, you can generate static sitemaps file at `public/sitemap.xml` and `public/sitemap_model-name.xml`.

## Frontend architecture
Frontend follows a component approach build with Laravel Blade templating engine.

Frontend architecture is defined in two different places:
- resources/frontend, for assets, scripts and styles files,
- `resources/views/site` for pages and `resources/views/site` for components

Global aliases for Blade components have been set:
- layouts
- partials
- forms
  You can use them to import components, example: <x-partials::head-assets/>

Following libraries have been installed and pre-setup:

- Tailwind css
- A17 Tailwind plugins
- A17 JS Helpers
- A17 Behaviors

With A17 Tailwind plugins, you can define all your frontend configuration inside `frontend.config.json`. This file is shared with current Tailwind config and also shared as a global variable **FE** into each Blade file.

### Vitrine Components

Some components are directly imported from Vitrine Blade UI and styled trough Vitrine components JSON files inside ``resources/frontend/vitrine-ui``.
Because this library is still under active development, we are currently connected to associated dev branch ``raw-components``.

### Installation
To install and build frontend dependencies, you can run the following commands. Depending on your configuration, you can run them locally or inside a docker container.

Frontend is build with [Vite](https://vitejs.dev/) and [Laravel Vite plugin](https://github.com/laravel/vite-plugin) and requires Node(16+) and NPM(8+).

```shell
# Install dependencies
$ npm install

# Bundle dependencies in dev mode with HMR enable
$ npm run dev

# Bundle dependencies for production
$ npm run build
```

## Linters and code formatters
We are using a series of tools to prettify and lint the code we write:

- Husky: to install and run the pre-commit hooks
- PHPStan: to do static analysis check on PHP code
- PHP-CS-Fixer: to remove unused dependencies and do some basic formatting
- Prettier: to fully format the code
- Eslint: to find and fix problems in JavaScript files
- Stylelint: to find and fix problems in CSS files
- Git conflict markers: the pre-commit checker tool also checks if the developer didn't stage any Git conflicted files by looking for conflict markers on the staged files.

These tools are executed automatically on every commit, only on staged files (except for Blast), and for it to work you need to make sure you executed. Composer and NPM are responsible for making sure husky is installed. And these commands are also available if a developer needs to run the commands manually:

Global commands:

- sh tools/linters.sh lint
- sh tools/linters.sh format

Specific commands:

- sh tools/linters.sh phpstan
- sh tools/linters.sh eslint
- sh tools/linters.sh prettify
- sh tools/linters.sh blast
- sh tools/linters.sh php-cs-fixer

Commands execution also generates a log file with the result at tools/logs/<tool-name>.log, you can check for PHPStan errors on this file, for example.
Linter commands have available aliases and can be run through Composer or NPM. For more details take a look to composer.json file and package.json file.

### PHPStan and Laravel Models
In order to run static analysis on models, phpstan needs to know schema of your model.
Those schemas are generated with [`barryvdh/laravel-ide-helper`](https://github.com/barryvdh/laravel-ide-helper?tab=readme-ov-file#automatic-phpdocs-for-models) package after a migration is done.

If you need to generate them manually, you can run the following command:

```shell
$ php artisan ide-helper:models --write-mixin
```

### Editors
We highly recommend you to configure your editor to take in account
current linters configurations files.

## Glide class override

The Glide class is temporarly being overriden by Composer, to disable this override just remove this line from the `composer.json` file:

```json
"psr-4": {
    ...
    "A17\\Twill\\Services\\MediaLibrary\\": "app/Vendor/A17/Twill/Services/MediaLibrary"
}
```

If the removal should be permanent, also delete the `app/Vendor/A17/Twill/Services/MediaLibrary` folder.
