@php
    use App\Support\GalleryVideoEmbed;

    $platform = $video->platform;
    $youtubeId = $platform === GalleryVideoEmbed::YOUTUBE ? GalleryVideoEmbed::youtubeId($video->url) : null;
    $tiktokId = $platform === GalleryVideoEmbed::TIKTOK ? GalleryVideoEmbed::tiktokId($video->url) : null;
@endphp

<div class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    @if ($platform === GalleryVideoEmbed::YOUTUBE && $youtubeId)
        <div class="aspect-video w-full bg-slate-950">
            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" title="{{ $video->title }}" class="h-full w-full" allowfullscreen loading="lazy"></iframe>
        </div>
    @elseif ($platform === GalleryVideoEmbed::TWITTER)
        <div class="flex justify-center p-4">
            <blockquote class="twitter-tweet" data-dnt="true"><a href="{{ $video->url }}"></a></blockquote>
        </div>
    @elseif ($platform === GalleryVideoEmbed::TIKTOK && $tiktokId)
        <div class="flex justify-center p-4">
            <blockquote class="tiktok-embed" cite="{{ $video->url }}" data-video-id="{{ $tiktokId }}" style="max-width: 605px; min-width: 300px;">
                <section><a target="_blank" href="{{ $video->url }}" title="{{ $video->title }}">{{ $video->title }}</a></section>
            </blockquote>
        </div>
    @else
        <div class="flex aspect-video w-full items-center justify-center bg-slate-100 text-sm text-slate-500 dark:bg-slate-800 dark:text-slate-400">Video tidak dapat ditampilkan</div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        @if ($video->title)
            <p class="line-clamp-2 text-base font-black text-slate-900 dark:text-white">{{ $video->title }}</p>
        @endif
        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-3">
            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">{{ GalleryVideoEmbed::label($platform) }}</span>
            @if ($video->credit_account)
                <span class="text-xs text-slate-500 dark:text-slate-400">Credit: {{ $video->credit_account }}</span>
            @endif
        </div>
    </div>
</div>
