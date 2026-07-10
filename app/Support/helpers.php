<?php
use A17\Twill\Models\Block;
use App\Support\Constants;
use Illuminate\Support\Str;
use A17\Twill\Models\Contracts\TwillModelContract;

// region TEXT HELPERS
if (!function_exists('formatHTMLContent')) {
    /**
     * Formats HTML content by stripping tags and adding line breaks.
     * @param string $content
     * @param string $allowedTags
     * @return string
     */
    function formatHTMLContent(
        string $content,
        string $allowedTags = Constants::ALLOWED_HTML_TAGS,
    ): string {
        $content = trim($content);
        $content = strip_tags($content, $allowedTags);

        return nl2br($content);
    }
}

if (!function_exists('contentToParagraph')) {
    /**
     * Returns content wrapped in a paragraph tag if it's plain text.
     *
     * @param string $content
     * @param string $allowedTags
     * @return string
     */
    function contentToParagraph(
        string $content,
        string $allowedTags = Constants::ALLOWED_HTML_TAGS,
    ): string {
        if ($content === strip_tags($content, $allowedTags)) {
            $content = htmlspecialchars($content);
            $content = preg_replace(['/\n{2,}/m', '/\n/m'], ['</p><p>', '<br>'], trim($content));
            $content = nl2br($content);
            $content = wrapWithParagraph($content);
        }

        return $content;
    }
}

if (!function_exists('wrapWithParagraph')) {
    /**
     * Wraps text in paragraph tags if it's not already wrapped.
     * @param string $text
     * @return string
     */
    function wrapWithParagraph(string $text): string
    {
        return str_starts_with(trim($text), '<p>') ? $text : '<p>' . $text . '</p>';
    }
}
// endregion

// region LINKS
if (!function_exists('isExternalUrl')) {
    /**
     * Checks if a URL is external.
     * @param string $url
     * @return bool
     */
    function isExternalUrl(string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        if (Str::startsWith($url, ['#', '/'])) {
            return false;
        }

        $home = url('/');

        if (Str::startsWith($url, $home)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('mapTwillLink')) {
    /**
     * Maps a Twill link field to an array with URL, label, and external flag.
     * @param TwillModelContract $model
     * @param string|null $fieldsGroup
     * @param string $browserName
     * @return array
     */
    function mapTwillLink(
        TwillModelContract $model,
        ?string $fieldsGroup = null,
        string $browserName = 'linked_item',
    ): array {
        $type = getTwillLinkField('type', $model, $fieldsGroup);
        $label = getTwillLinkField('label', $model, $fieldsGroup);

        if ($type === 'internal') {
            $linkedItem = method_exists($model, 'getRelated') ? $model->getRelated($browserName)->first() : null;
            $label = $label ?? ($linkedItem->title ?? '');

            if (!$linkedItem) {
                $url = $label ? '#' : null;
            } else {
                $url = getTwillModelFrontendUrl($linkedItem);
            }

            return [
                'url' => $url,
                'label' => $label,
                'external' => false,
                'variant' => getTwillLinkField('variant', $model, $fieldsGroup) ?? null,
            ];
        }

        $url = getTwillLinkField('url', $model, $fieldsGroup);

        return [
            'url' => $url,
            'label' => $label,
            'external' => true,
            'variant' => getTwillLinkField('variant', $model, $fieldsGroup) ?? null,
        ];
    }
}

if (!function_exists('getTwillLinkField')) {
    function getTwillLinkField(string $field, TwillModelContract $model, ?string $fieldsGroup): ?string
    {
        if ($model instanceof Block) {
            return $model->input($field);
        }

        return $fieldsGroup && ($model->{$fieldsGroup}[$field] ?? false)
            ? $model->{$fieldsGroup}[$field]
            : $model->$field;
    }
}

if (!function_exists('getTwillModelFrontendUrl')) {
    /**
     * Returns the frontend URL for a Twill model or mapped route.
     * @param TwillModelContract $linkedItem
     * @return string
     */
    function getTwillModelFrontendUrl(TwillModelContract $linkedItem): string
    {
        $routesMapping = Constants::LINKS_MAPPING;

        if (isset($routesMapping[$linkedItem->getMorphClass()])) {
            $mappedRoute = $routesMapping[$linkedItem->getMorphClass()];

            return route($mappedRoute['name'], ($mappedRoute['withSlug'] ?? false) && property_exists($linkedItem, 'slug') ? $linkedItem->slug : []);
        }

        return $linkedItem->getFullUrl();
    }
}

// endregion
