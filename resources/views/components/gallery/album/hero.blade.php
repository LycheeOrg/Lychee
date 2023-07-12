<div class="w-full h-1/2">
    <img class="absolute block top-0 left-0 w-full h-1/2 object-cover object-center z-0" src="{{ URL::asset($url) }}">
    <div class="h-full pl-7 pt-7 relative text-shadow-sm w-full bg-gradient-to-b from-black/20 via-80%">
        <h1 class="font-bold text-4xl text-white">{{ $title }}</h1>
        @if($min_taken_at !== null)
        <span class="text-neutral-200 text-sm">{{ $min_taken_at }}
            @if($max_taken_at !== $min_taken_at)
                - {{ $max_taken_at }}
            @endif
        </span>
        @endif
        {{-- <div class="absolute flex flex-col bottom-0 right-0 w-full pl-7 pb-7 bg-gradient-to-t from-black/60 via-80%">
            <span class="block text-neutral-200 text-sm">{{ __('lychee.ALBUM_CREATED') }} {{ $this->album->created_at->format('M j, Y g:i:s A e') }}</span>
            @if($this->album->children->count() > 0)
                <span class="block text-neutral-200 text-sm">{{ $this->album->children->count() }} {{ __('lychee.ALBUM_SUBALBUMS') }}</span>
            @endif
            <span class="block text-neutral-200 text-sm">{{ $this->album->photos->count() }} {{ __('lychee.ALBUM_IMAGES') }}</span>
        </div> --}}
    </div>
</div>
