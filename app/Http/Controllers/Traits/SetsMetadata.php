<?php

namespace App\Http\Controllers\Traits;

use A17\Twill\Facades\TwillAppSettings;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Database\Eloquent\Model;
use CwsDigital\TwillMetadata\Traits\SetsMetadata as TwillMetadataSetsMetadata;

trait SetsMetadata
{
    use TwillMetadataSetsMetadata {
        TwillMetadataSetsMetadata::setMetadata as twillMetadataSetsMetadata;
    }

    public function setMetadata(Model $describable): void
    {
        $this->twillMetadataSetsMetadata($describable);

        // @phpstan-ignore-next-line
        $metadata = $describable->metadata;

        if (!$metadata) {
            return;
        } // Prevent errors if model has no attached metadata

        SEOTools::opengraph()->addProperty('locale', app()->getLocale());

        if ($metadata->field('og_title')) {
            SEOTools::opengraph()->setTitle($metadata->field('og_title'));
        }

        if ($metadata->field('card_type')) {
            SEOTools::twitter()->setType($metadata->field('card_type'));
        }

        if ($ogSiteName = TwillAppSettings::getTranslated('seo.metadata.site_title')) {
            SEOTools::opengraph()->setSiteName($ogSiteName);
        }

        if ($siteName = TwillAppSettings::getTranslated('seo.metadata.site_twitter')) {
            SEOTools::twitter()->setSite($siteName);
        }
    }
}
