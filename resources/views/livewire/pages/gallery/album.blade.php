<div class="w-full"
    x-data="albumView(
        @entangle('sessionFlags.nsfwAlbumsVisible'),
        @entangle('sessionFlags.is_fullscreen'),
        @js($flags->can_edit),
        @js($album->id),
        @js($this->albumIDs),
        @js($this->photoIDs)
        )"
    @keydown.window="handleKeydown(event, $wire)"
    >
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back />
        <x-header.title>{{ $album->title }}</x-header.title>
        {{-- <a class="button button--map" id="button_map_album"><x-icons.iconic icon="map" /></a> --}}
        @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
            @if ($flags->is_base_album)
                <x-header.button x-on:click="detailsOpen = false" icon="chevron-top" fill="fill-sky-500" x-cloak x-show="detailsOpen" />
                <x-header.button x-on:click="detailsOpen = true" icon="chevron-bottom" x-cloak x-show="!detailsOpen" />
            @endif
        @endcan
        @can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, $this->album])
            <x-header.button wire:click="openContextMenu" icon="plus" />
        @endcan
    </x-header.bar>
    @if($flags->is_password_protected)
        <livewire:gallery.album.unlock albumID="$albumId" />
    @elseif(!$flags->is_accessible)
        <div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
	bg-black/80 z-50 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
            data-closable="true"
            x-data="{loginModalOpen:true}"
            >
            <div class="basicModal transition-opacity ease-in duration-1000
                opacity-100 bg-gradient-to-b from-dark-300 to-dark-400
                relative w-[500px] text-sm rounded-md text-neutral-400 animate-moveUp
                "
                role="dialog">
                <livewire:modals.login />
            </div>
        </div>
    @else
    <div id="lychee_view_content"
        @if ($flags->layout() === \App\Enum\AlbumLayoutType::JUSTIFIED)
        x-init="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                $wire.loadAlbum(width - 2 * 28 - 20);" {{-- We remove 2x padding of 7rem + 20px for the scroll bar --}}
        x-on:resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                $wire.loadAlbum(width - 2*28);"
        @endif
        class="relative flex flex-wrap content-start w-full justify-start overflow-x-clip overflow-y-auto"
        x-bind:class="isFullscreen ? 'h-[calc(100vh-3px)]' : 'h-[calc(100vh-56px)]'">
        @if ($flags->is_base_album)
        @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
        <x-gallery.album.menu.menu :album="$this->album" :userCount="$num_users" />
        @endcan
        @endif
        @if($num_children > 0 || $num_photos > 0)
        <x-gallery.album.hero    :album="$this->album" :url="$this->header_url" x-show="! detailsOpen" />
        <x-gallery.album.details :album="$this->album" :url="$this->header_url" x-show="! detailsOpen" />
        @endif
        @if($num_children === 0 && $num_photos === 0)
        <span class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
            Nothing to see here
        </span>
        @endif
        @if($num_children > 0 && $num_photos > 0)<x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />@endif
        @if($num_children > 0)
            @foreach ($this->album->children as $data)<x-gallery.album.thumbs.album :data="$data" />@endforeach
        @endif
        @if($num_children > 0 && $num_photos > 0)<x-gallery.divider title="{{ __('lychee.PHOTOS') }}" />@endif
        @if ($flags->is_ready_to_load || $flags->layout() !== \App\Enum\AlbumLayoutType::JUSTIFIED)
            <div
                @class(['relative w-full',
                    'm-4 flex flex-wrap' => $flags->layout() === \App\Enum\AlbumLayoutType::SQUARE,
                    'm-7' => $flags->layout() === \App\Enum\AlbumLayoutType::JUSTIFIED,
                    'masondry' => $flags->layout() === \App\Enum\AlbumLayoutType::MASONRY,
                    'grid' => $flags->layout() === \App\Enum\AlbumLayoutType::GRID,
                ])
                @if ($flags->layout() === \App\Enum\AlbumLayoutType::JUSTIFIED)
                    style="height:{{ $this->geometry->containerHeight }}px;"
                @endif
            >
            @for ($i = 0; $i < $num_photos; $i++)
                <x-gallery.album.thumbs.photo :data="$this->album->photos[$i]" albumId="{{ $albumId }}" :geometry="$this->geometry?->boxes->get($i)" :layout="$flags->layout()" />
            @endfor
            </div>
        @else
        <span
            x-init="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                    $wire.loadAlbum(width - 2 * 28 - 20);" {{-- We remove 2x padding of 7rem + 20px for the scroll bar --}}
            x-on:resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                                $wire.loadAlbum(width - 2*28);"
            class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
            Loading...
        </span>
        @endif
    </div>
    <x-gallery.album.sharing-links :album="$this->album" x-show="sharingLinksOpen" />
    @endif
</div>
