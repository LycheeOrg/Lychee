<div id="lychee_view_content" 
    x-init="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    $wire.loadAlbum(width - 2*28 -20);"
    {{-- We remove 2x padding of 7rem + 20px for the scroll bar --}}
    x-on:resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    $wire.loadAlbum(width - 2*28);"
    class="relative flex flex-wrap content-start w-full justify-start h-[calc(100vh-56px)] overflow-x-clip overflow-y-auto"
    >

@if(!$isOpen)
    @if ($this->header_url !== null)
        <x-gallery.album.hero :album="$this->album" url="{{ $this->header_url }}" />
    @endif
    <div class="w-full px-7 my-4 flex flex-row-reverse">
        <div class="order-1 flex flex-col w-full">
            @if($this->header_url === null )
                <h1 class="font-bold text-2xl text-white">{{ $this->album->title }}</h1>
            @endif
            <span class="block text-neutral-200 text-sm">
                {{ __('lychee.ALBUM_CREATED') }} {{ $this->album->created_at->format('M j, Y g:i:s A e') }}
            </span>
            @if($this->album->children->count() > 0)
                <span class="block text-neutral-200 text-sm">
                    {{ $this->album->children->count() }} {{ __('lychee.ALBUM_SUBALBUMS') }}
                </span>
            @endif
            @if($this->album->photos->count() > 0)
                <span class="block text-neutral-200 text-sm">
                    {{ $this->album->photos->count() }} {{ __('lychee.ALBUM_IMAGES') }}
                </span>
            @endif
        </div>
        @can(App\Policies\AlbumPolicy::CAN_DOWNLOAD, [App\Contracts\Models\AbstractAlbum::class, $this->album])
        <a class="flex-shrink-0 px-3 cursor-pointer"
            title="{{ __('lychee.DOWNLOAD_ALBUM') }}"
            href="{{ route('download', ['albumIDs' => $this->album->id]) }}" >
            <x-icons.iconic class="my-0 w-4 h-4 mr-0 ml-0" icon="cloud-download" />
        </a>
        @endcan
        <a class="flex-shrink-0 px-3 cursor-pointer" title={{ __('lychee.SHARE_ALBUM') }} wire:click="openSharingModal">
            <x-icons.iconic class="my-0 w-4 h-4 mr-0 ml-0" icon="share-ion" />
        </a>
    </div>
    @if($this->album->description !== null)
    <div class="w-full px-7 my-4 text-justify text-neutral-200 markdown">
        @markdown{{ $this->album->description }}@endmarkdown
    </div>
    @endif
@else
    <livewire:forms.album.menu :album="$this->album" />
@endif
@php
    Helpers::data_index_set(100);
@endphp
@if ($ready_to_load)
    @isset($this->album->children)
        @if ($this->album->children->count() > 0)
            @if ($this->album->photos?->count() > 0)
                <x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />
            @endif
            @foreach ($this->album->children as $data)
                <x-gallery.album.thumbs.album :data="$data" />
            @endforeach
            @if ($this->album->photos?->count() > 0)
                <x-gallery.divider title="{{ __('lychee.PHOTOS') }}" />
            @endif
        @endif
    @endisset
    @if ($this->album->photos?->count() > 0)
    <div 
        @class([
            'squares' => $layout === \App\Enum\Livewire\AlbumMode::SQUARE,
            'm-7 relative w-full' => $layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED, // only one working for now
            'masondry' => $layout === \App\Enum\Livewire\AlbumMode::MASONRY,
            'grid' => $layout === \App\Enum\Livewire\AlbumMode::GRID,
        ])
        @if ($layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED)
            style="height:{{ $this->geometry->containerHeight }}px;"
        @endif
        >
        @for ($i = 0; $i < $this->album->photos->count(); $i++)
            <x-gallery.album.thumbs.photo :data="$this->album->photos[$i]" :geometry="$this->geometry->boxes->get($i)" />
        @endfor
    </div>
    @endif
@else
    <span class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
    Loading...
    </span>
@endif
<x-footer />
</div>
