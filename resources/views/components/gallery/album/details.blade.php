<div class="w-full px-7 my-4 flex flex-row-reverse" {{ $attributes }} x-transition.opacity >
    <div class="order-1 flex flex-col w-full">
        @if($this->AlbumFormatted->url === null )
            <h1 class="font-bold text-2xl text-text-main-0">{{ $this->title }}</h1>
        @endif
        @if($this->AlbumFormatted->created_at !== null)
        <span class="block text-text-main-200 text-sm">
            {{ __('lychee.ALBUM_CREATED') }} {{ $this->AlbumFormatted->created_at }}
        </span>
        @endif
        @if($this->num_albums > 0)
            <span class="block text-text-main-200 text-sm">
                {{ $this->num_albums }} {{ __('lychee.ALBUM_SUBALBUMS') }}
            </span>
        @endif
        @if($this->num_photos > 0)
            <span class="block text-text-main-200 text-sm">
                {{ $this->num_photos }} {{ __('lychee.ALBUM_IMAGES') }}
                @if($this->AlbumFormatted->license !== '')
                <span class="text-text-main-400 text-sm">
                    &mdash; {{ $this->AlbumFormatted->license }}
                </span>
                @endif
            </span>
        @endif
    </div>
    @if($this->rights->can_download)
    <a class="flex-shrink-0 px-3 cursor-pointer"
        title="{{ __('lychee.DOWNLOAD_ALBUM') }}"
        href="{{ route('download', ['albumIDs' => $this->albumId]) }}" >
        <x-icons.iconic class="my-0 w-4 h-4 mr-0 ml-0" icon="cloud-download" />
    </a>
    @endif
    <a class="flex-shrink-0 px-3 cursor-pointer" title={{ __('lychee.SHARE_ALBUM') }} x-on:click="albumFlags.isSharingLinksOpen = true">
        <x-icons.iconic class="my-0 w-4 h-4 mr-0 ml-0" icon="share-ion" />
    </a>
</div>
@if($this->AlbumFormatted->description !== null)
<div class="w-full px-7 my-4 text-justify text-text-main-200 prose prose-invert prose-sm" {{ $attributes }} >
    @markdown{{ $this->AlbumFormatted->description }}@endmarkdown
</div>
@endif