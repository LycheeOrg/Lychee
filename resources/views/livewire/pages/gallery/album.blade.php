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
                <div class="relative w-full h-0 -translate-y-5 text-right pr-7" x-data="{ layout: $wire.entangle('flags.layout') }" >
                    <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="layout = 'square'"
                    title="{{ __('lychee.LAYOUT_SQUARES') }}" >
                        <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300
                        group-hover:scale-150 group-hover:stroke-white"
                        fill="" icon="squares"
                        x-bind:class="layout === 'square' ? 'stroke-sky-400' : 'stroke-neutral-400'" 
                        />
                    </a>
                    <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="layout = 'justified'"
                    title="{{ __('lychee.LAYOUT_JUSTIFIED') }}" >
                        <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300
                        group-hover:scale-150 group-hover:fill-white"
                        fill="" icon="justified"
                        x-bind:class="layout === 'justified' ? 'fill-sky-400' : 'fill-neutral-400'" 
                        />
                    </a>
                    <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="layout = 'masonry'"
                    title="{{ __('lychee.LAYOUT_MASONRY') }}" >
                        <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300
                        group-hover:scale-150 group-hover:stroke-white"
                        fill="" icon="masonry"
                        x-bind:class="layout === 'masonry' ? 'stroke-sky-400' : 'stroke-neutral-400'" 
                         />
                    </a>
                    <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="layout = 'grid'"
                    title="{{ __('lychee.LAYOUT_GRID') }}" >
                        <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300
                        group-hover:scale-150 group-hover:stroke-white"
                        fill="" icon="grid"
                        x-bind:class="layout === 'grid' ? 'stroke-sky-400' : 'stroke-neutral-400'" 
                         />
                    </a>
                </div>
                <div @class([
                    'relative w-full',
                    'm-4 flex flex-wrap' => $flags->layout() === \App\Enum\AlbumLayoutType::SQUARE,
                    'm-7 grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4' => $flags->layout() !== \App\Enum\AlbumLayoutType::SQUARE
                ])
                @if ($flags->layout() === AlbumLayoutType::JUSTIFIED)
                    x-justify
                @elseif ($flags->layout() === AlbumLayoutType::MASONRY)
                    x-masonry
                @elseif ($flags->layout() === AlbumLayoutType::GRID)
                    x-grid
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
