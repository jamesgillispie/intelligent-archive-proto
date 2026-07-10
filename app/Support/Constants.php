<?php

namespace App\Support;

class Constants
{
    // region LINKS
    public const LINKS_MAPPING = [
        'contactLanding' => [
            'name' => 'contact.index',
        ],
        'aboutLanding' => [
            'name' => 'about.index',
        ],
        'careersLanding' => [
            'name' => 'careers.index',
        ],
        'homepage' => [
            'name' => 'home',
        ],
        'articles' => [
            'name' => 'articles.show',
            'withSlug' => true,
        ],
        'pages' => [
            'name' => 'pages.show',
            'withSlug' => true,
        ],
    ];
    // endregion

    // region TEXT

    /**
     * Define the allowed HTML tags for rich text.
     * Used as default value for text helpers
     */
    public const ALLOWED_HTML_TAGS = '<p><a><strong><em><ul><ol><li><br><hr><h1><h2><h3><h4><h5><h6><table><thead><tbody><tfoot><tr><th><td><span class="sr-only">';
    // endregion
}
