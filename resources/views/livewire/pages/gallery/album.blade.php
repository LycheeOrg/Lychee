<div class="w-full" x-data="{ detailsOpen: false }">
    <!-- toolbar -->
    <x-header.bar>
        <x-header.button @keydown.escape.window="$wire.back()" wire:click="back"  icon="chevron-left" />
        <x-header.title>{{ $album->title }}</x-header.title>
        {{-- <a class="button button--map" id="button_map_album"><x-icons.iconic icon="map" /></a> --}}
        {{-- <a class="button" id="button_fs_album_enter"><x-icons.iconic icon="fullscreen-enter" /></a> --}}
        {{-- <a class="button" id="button_fs_album_exit"><x-icons.iconic icon="fullscreen-exit" /></a> --}}
        {{-- <a class="header__divider"></a> --}}
        @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
            @if ($flags->is_base_album)
                <x-header.button x-on:click="detailsOpen = ! detailsOpen" icon="chevron-top" x-cloak x-show="detailsOpen" />
                <x-header.button x-on:click="detailsOpen = ! detailsOpen" icon="chevron-bottom" x-cloak x-show="!detailsOpen" />
            @endif
        @endcan
        @can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, $this->album])
            <x-header.button wire:click="openContextMenu" icon="plus" />
        @endcan
    </x-header.bar>
    <div id="lychee_view_content"
        x-init="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                $wire.loadAlbum(width - 2 * 28 - 20);" {{-- We remove 2x padding of 7rem + 20px for the scroll bar --}}
        x-on:resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                $wire.loadAlbum(width - 2*28);"
        class="relative flex flex-wrap content-start w-full justify-start h-[calc(100vh-56px)] overflow-x-clip overflow-y-auto">

        @if ($flags->is_base_album)
        @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
        <x-gallery.album.menu.menu :album="$this->album" :userCount="User::count()" />
        @endcan
        @endif
        @if($num_children > 0 || $num_photos > 0)
        <x-gallery.album.hero    :album="$this->album" :url="$this->header_url" x-show="! detailsOpen" />
        <x-gallery.album.details :album="$this->album" :url="$this->header_url" x-show="! detailsOpen" />
        @endif

        @if ($flags->is_ready_to_load)
            @if($num_children > 0 && $num_photos > 0)<x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />@endif
            @if($num_children > 0)
                @foreach ($this->album->children as $data)<x-gallery.album.thumbs.album :data="$data" />@endforeach
            @endif
            @if($num_children > 0 && $num_photos > 0)<x-gallery.divider title="{{ __('lychee.PHOTOS') }}" />@endif
            @if($num_photos > 0)
            <div @class([
                    'squares' => $layout === \App\Enum\Livewire\AlbumMode::SQUARE,
                    'm-7 relative w-full' => $layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED, // only one working for now
                    'masondry' => $layout === \App\Enum\Livewire\AlbumMode::MASONRY,
                    'grid' => $layout === \App\Enum\Livewire\AlbumMode::GRID,
                ])
                @if ($layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED)
                    style="height:{{ $this->geometry->containerHeight }}px;"
                @endif
            >
            @for ($i = 0; $i < $num_photos; $i++)
                <x-gallery.album.thumbs.photo :data="$this->album->photos[$i]" albumId="{{ $albumId }}" :geometry="$this->geometry->boxes->get($i)" />
            @endfor
            </div>
            @endif
            @if($num_children === 0 && $num_photos === 0)
            <span class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
                Loading...
            </span>
            @endif
        @else
        <span class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
            Loading...
        </span>
        @endif
    </div>
</div>
