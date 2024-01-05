@if ($this->AlbumFormatted->url !== null)
<div class="w-full h-1/2 text-text-main-200 relative" {{ $attributes }} >
    <img class="absolute block top-0 left-0 w-full h-full object-cover object-center z-0" src="{{ URL::asset($this->AlbumFormatted->url) }}">
    <div class="h-full pl-7 pt-7 relative text-shadow-sm w-full bg-gradient-to-b from-black/20 via-80%">
        <h1 class="font-bold text-4xl text-text-main-0">{{ $this->title }}</h1>
        @if($this->AlbumFormatted->min_taken_at !== null)
        <span class="text-text-main-200 text-sm">{{ $this->AlbumFormatted->min_taken_at }}
            @if($this->AlbumFormatted->max_taken_at !== $this->AlbumFormatted->min_taken_at)
                - {{ $this->AlbumFormatted->max_taken_at }}
            @endif
        </span>
        @endif
    </div>
</div>
@endif