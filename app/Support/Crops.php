<?php

namespace App\Support;

class Crops
{
    public const CROP_NAMES = [
        'default' => 'default',
        'free_ratio' => 'free_ratio',
        'square' => 'square',
        'landscape' => 'landscape',
        'portrait' => 'portrait',
        '16:9' => '16:9',
        '3:2' => '3:2',
        '4:3' => '4:3',
        '1:1' => '1:1',
        'mobile' => 'mobile',
        'tablet' => 'tablet',
        'desktop' => 'desktop',
    ];

    public const SQUARE = [
        'name' => self::CROP_NAMES['square'],
        'ratio' => Ratios::_1X1,
    ];

    public const _16_9 = [
        'name' => self::CROP_NAMES['landscape'],
        'ratio' => Ratios::_16X9,
    ];

    public const _3_2 = [
        'name' => self::CROP_NAMES['portrait'],
        'ratio' => Ratios::_3X2,
    ];

    public const _4_3 = [
        'name' => self::CROP_NAMES['4:3'],
        'ratio' => Ratios::_4X3,
    ];

    public static function setCrop(array $config = [], string $cropName = self::CROP_NAMES['default']): array
    {
        $crop[$cropName] = [$config];
        return $crop;
    }
}
