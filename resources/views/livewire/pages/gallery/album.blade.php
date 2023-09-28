<div class="w-full" x-data="albumView(
    @entangle('sessionFlags.nsfwAlbumsVisible'),
    @entangle('sessionFlags.is_fullscreen'),
    @js($flags->can_edit),
    @js($album->id),
    @js($this->albumIDs),
    @js($this->photoIDs)
)" @keydown.window="handleKeydown(event)">
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back />
        <x-header.title>{{ $album->title }}</x-header.title>
        {{-- <a class="button button--map" id="button_map_album"><x-icons.iconic icon="map" /></a> --}}
        <x-header.actions-menus />
        {{-- <x-header.button wire:click="openContextMenu" icon="plus" /> --}}
        {{-- @endcan --}}
        @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
            @if ($flags->is_base_album)
                <x-header.button x-on:click="detailsOpen = false" icon="chevron-top" fill="fill-sky-500" x-cloak
                    x-show="detailsOpen" />
                <x-header.button x-on:click="detailsOpen = true" icon="chevron-bottom" x-cloak x-show="!detailsOpen" />
            @endif
        @endcan
    </x-header.bar>
    @if ($flags->is_password_protected)
        <livewire:gallery.album.unlock albumID="$albumId" />
    @elseif(!$flags->is_accessible)
        <x-gallery.album.login-dialog />
    @else
        <div id="lychee_view_content"
            class="relative flex flex-wrap content-start w-full justify-start overflow-x-clip overflow-y-auto"
            x-bind:class="isFullscreen ? 'h-[calc(100vh-3px)]' : 'h-[calc(100vh-56px)]'">
            @if ($flags->is_base_album)
                @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
                    <x-gallery.album.menu.menu :album="$this->album" :userCount="$num_users" />
                @endcan
            @endif
            @if ($num_children > 0 || $num_photos > 0)
                <x-gallery.album.hero :album="$this->album" :url="$this->header_url" x-show="! detailsOpen" />
                <x-gallery.album.details :album="$this->album" :url="$this->header_url" x-show="! detailsOpen" />
            @endif
            @if ($num_children === 0 && $num_photos === 0)
                <span class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
                    Nothing to see here
                </span>
            @endif
            @if ($num_children > 0 && $num_photos > 0)
                <x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />
            @endif
            @if ($num_children > 0)
                @foreach ($this->album->children as $data)
                    <x-gallery.album.thumbs.album :data="$data" />
                @endforeach
            @endif
            @if ($num_children > 0 && $num_photos > 0)
                <x-gallery.divider title="{{ __('lychee.PHOTOS') }}" />
            @endif
                <div @class([
                    'relative w-full',
                    'm-4 flex flex-wrap' =>
                        $flags->layout() === \App\Enum\AlbumLayoutType::SQUARE,
                    'm-7' => $flags->layout() === \App\Enum\AlbumLayoutType::JUSTIFIED,
                    'm-7 grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4' => $flags->layout() === \App\Enum\AlbumLayoutType::MASONRY,
                    'grid' => $flags->layout() === \App\Enum\AlbumLayoutType::GRID,
                ])
                @if ($flags->layout() === \App\Enum\AlbumLayoutType::JUSTIFIED)
                    x-justify
                @elseif ($flags->layout() === \App\Enum\AlbumLayoutType::MASONRY)
                    x-masonry
                @endif
                >
                @foreach ($this->album->photos as $photo)
                    <x-gallery.album.thumbs.photo :data="$photo" albumId="{{ $albumId }}" :layout="$flags->layout()" />
                @endforeach
            </div>
            <livewire:pages.gallery.sensitive-warning :album="$this->album" />
        </div>
        <x-gallery.album.sharing-links :album="$this->album" x-show="sharingLinksOpen" />
    @endif
</div>
