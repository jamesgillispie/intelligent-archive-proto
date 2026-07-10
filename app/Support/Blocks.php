<?php

namespace App\Support;

use App\View\Components\Twill\Blocks\Link;
use App\View\Components\Twill\Blocks\Text;
use App\View\Components\Twill\Blocks\Embed;
use App\View\Components\Twill\Blocks\Image;
use App\View\Components\Twill\Blocks\Quote;
use App\View\Components\Twill\Blocks\Video;
use App\View\Components\Twill\Blocks\Separator;
use App\View\Components\Twill\Blocks\SimpleImage;
use App\View\Components\Twill\Blocks\SimpleVideo;

class Blocks
{
    public const ALL_BLOCKS = [
        Embed::class,
        Text::class,
        Link::class,
        Quote::class,
        Separator::class,
        SimpleImage::class,
        SimpleVideo::class,
    ];

    public const LANDING_BLOCK_NAMES = [Image::class, Video::class];
    public const EDITORIAL_BLOCK_NAMES = [Text::class, SimpleImage::class, SimpleVideo::class];
}
