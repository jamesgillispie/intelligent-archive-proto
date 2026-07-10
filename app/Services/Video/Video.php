<?php

namespace App\Services\Video;

class Video
{
    public function getType(string $url): string
    {
        if (!empty($url)) {
            $parts = parse_url($url);
            if (isset($parts['host'])) {
                if (stripos($parts['host'], 'youtube') !== false) {
                    return 'youtube';
                } elseif (stripos($parts['host'], 'vimeo') !== false) {
                    return 'vimeo';
                }
            }
        }

        return '';
    }

    public function getEmbedQueryString(string $url): array
    {
        $output = [];

        if (!empty($url)) {
            $parts = parse_url($url);
            if (isset($parts['query'])) {
                $query = $parts['query'];
                parse_str($query, $output);
            }
        }

        // Remove v url param if found
        if (isset($output['v'])) {
            unset($output['v']);
        }

        return $output;
    }

    public function getId(string $url): ?string
    {
        $type = $this->getType($url);

        return match ($type) {
            'youtube' => $this->getYouTubeIdCode($url),
            'vimeo' => $this->getVimeoIdCode($url),
            default => null,
        };
    }

    public function getYoutubeThumbnailImage(string $url): ?string
    {
        if ($id = $this->getYouTubeIdCode($url)) {
            return 'https://img.youtube.com/vi/' . $id . '/mqdefault.jpg';
        }

        return null;
    }

    public function getYouTubeIdCode(string $url): ?string
    {
        preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
            $url,
            $match,
        );

        return $match[1] ?? null;
    }

    public function getVimeoIdCode(string $url): string
    {
        if (preg_match('~^http[s]://(?:www\.)?vimeo\.com/(?:clip:)?(\d+)~', $url, $match)) {
            $videoId = $match[1];
        } else {
            $videoId = substr($url, 10, strlen($url));
        }

        return $videoId;
    }
}
