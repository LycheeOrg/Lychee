<div class="w-full" x-data="{
        detailsOpen: false,
        sharingLinksOpen: false,
        nsfwAlbumsVisible: @entangle('sessionFlags.nsfwAlbumsVisible'),
        isFullscreen: @entangle('sessionFlags.is_fullscreen'),
        silentToggle(elem) {
            this[elem] = ! this[elem];
            $wire.silentUpdate();
        }
    }" 
    @keydown.window="
        if (event.keyCode === 72 && !detailsOpen) { event.preventDefault(); silentToggle('nsfwAlbumsVisible'); }
        if (event.keyCode === 73 && $focus.focused() === undefined) { event.preventDefault(); detailsOpen = !detailsOpen; }
        if (event.keyCode === 70 && $focus.focused() === undefined) { event.preventDefault(); silentToggle('isFullscreen') }
    {{-- 72 = h --}}
    {{-- 73 = i --}}
    {{-- 70 = f --}}
    ">
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back back="if (detailsOpen) { detailsOpen = false; } else { $wire.back(); }" />
        <x-header.title>{{ $album->title }}</x-header.title>
        {{-- <a class="button button--map" id="button_map_album"><x-icons.iconic icon="map" /></a> --}}
        {{-- <a class="button" id="button_fs_album_enter"><x-icons.iconic icon="fullscreen-enter" /></a> --}}
        {{-- <a class="button" id="button_fs_album_exit"><x-icons.iconic icon="fullscreen-exit" /></a> --}}
        {{-- <a class="header__divider"></a> --}}
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
    @if(!$flags->is_locked)
    <div id="lychee_view_content"
        @if ($flags->layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED)
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
        @if ($flags->is_ready_to_load || $flags->layout !== \App\Enum\Livewire\AlbumMode::JUSTIFIED->value)
            <div
                @class(['relative w-full',
                    'm-4 flex flex-wrap' => $flags->layout === \App\Enum\Livewire\AlbumMode::SQUARE->value,
                    'm-7' => $flags->layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED->value,
                    'masondry' => $flags->layout === \App\Enum\Livewire\AlbumMode::MASONRY->value,
                    'grid' => $flags->layout === \App\Enum\Livewire\AlbumMode::GRID->value,
                ])
                @if ($flags->layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED->value)
                    style="height:{{ $this->geometry->containerHeight }}px;"
                @endif
            >
            @for ($i = 0; $i < $num_photos; $i++)
                <x-gallery.album.thumbs.photo :data="$this->album->photos[$i]" albumId="{{ $albumId }}" :geometry="$this->geometry?->boxes->get($i)" :layout="\App\Enum\Livewire\AlbumMode::from($flags->layout)" />
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
    @else
    <x-gallery.album.unlock />
    @endif
</div>
