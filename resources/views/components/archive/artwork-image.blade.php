@props([
    'artwork',
    'widths' => [440, 880],
    'sizes' => '(min-width: 900px) 25vw, 50vw',
    'eager' => false,
    'imgClass' => 'h-full w-full object-contain',
    'placeholder' => '',
])

@php
    /* Fallback chain: downloaded media (Glide, resized locally) → remote
       IIIF (not yet downloaded) → quiet placeholder (failed or no
       open-access image). Paintings are never cropped — fit=max only. */
    $role = \App\Twill\Capsules\Artworks\Models\Artwork::IMAGE_ROLE;
    $hasLocal = $artwork->hasImage($role);
    $remote = $hasLocal ? null : $artwork->primaryImage;
    $hasRemote = $remote !== null && $remote->iiif_url !== null && !$remote->hasFailed();

    $src = null;
    $srcset = null;
    $alt = '';

    if ($hasLocal) {
        $src = $artwork->image($role, 'default', ['w' => $widths[0]]);
        $srcset = collect($widths)
            ->map(fn(int $width) => $artwork->image($role, 'default', ['w' => $width]) . " {$width}w")
            ->join(', ');
        $alt = $artwork->imageAltText($role) ?: $artwork->title;
    } elseif ($hasRemote) {
        $src = $remote->sizedUrl($widths[0]);
        $srcset = collect($widths)->map(fn(int $width) => $remote->sizedUrl($width) . " {$width}w")->join(', ');
        $alt = $remote->altText($artwork->title);
    }
@endphp

@if ($src !== null)
    <img class="{{ $imgClass }}" src="{{ $src }}" srcset="{{ $srcset }}" alt="{{ $alt }}"
        sizes="{{ $sizes }}" @if ($eager) fetchpriority="high" @else loading="lazy" @endif />
@elseif ($placeholder !== '')
    <div class="f-ui-01 flex h-full w-full items-center justify-center bg-[rgba(0,0,0,0.04)] opacity-60">
        {{ $placeholder }}
    </div>
@else
    <div class="h-full w-full bg-[rgba(0,0,0,0.04)]"></div>
@endif
