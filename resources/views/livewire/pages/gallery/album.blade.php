<div class="w-full" x-data="albumView(
    @js(route('livewire-gallery')),
    @entangle('sessionFlags.nsfwAlbumsVisible'),
    @entangle('sessionFlags.is_fullscreen'),
    @entangle('sessionFlags.are_photo_details_open'),
    @js($rights),
    @js($album->id),
    @js($this->albumIDs),
    @js($this->photosResource),
    @js($photoId),
    @js($this->overlayType),
    @js($this->layouts)
)" @keydown.window="handleKeydown(event)" @popstate.window="handlePopState(event)">
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back wire:navigate href="{{ $this->back }}" />
        <x-header.title>{{ $this->title }}</x-header.title>
        @if($is_search_accessible)
        <x-header.button class="flex flex-grow justify-end" href="{{ route('livewire-search', ['albumId' => $this->album->id]) }}" wire:navigate icon="magnifying-glass" />
        @endif
        <x-header.actions-menus />
        @if ($rights->can_edit && $flags->is_base_album)
            <x-header.button x-on:click="toggleDetails" icon="chevron-top" fill="fill-primary-500" x-cloak
                x-show="albumFlags.isDetailsOpen" />
            <x-header.button x-on:click="toggleDetails" icon="chevron-bottom" x-cloak
                x-show="!albumFlags.isDetailsOpen" />
        @endif
    </x-header.bar>
    @if ($flags->is_password_protected)
        <livewire:forms.album.unlock-album :albumID="$albumId" :back="$this->back" />
    @elseif(!$flags->is_accessible)
        <x-gallery.album.login-dialog />
    @else
        <div id="lychee_view_content"
            class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto"
            x-bind:class="isFullscreen ? 'h-[calc(100vh-3px)]' : 'h-[calc(100vh-56px)]'">
            @if ($rights->can_edit && $flags->is_base_album)
                <x-gallery.album.menu.menu :album="$this->album" :rights="$this->rights" :user-count="$this->num_users" />
            @endif
            @if ($this->albumFormatted !== null && ($num_albums > 0 || $num_photos > 0))
                <x-gallery.album.hero x-show="! albumFlags.isDetailsOpen" />
                <x-gallery.album.details x-show="! albumFlags.isDetailsOpen" />
            @endif
            @if ($num_albums === 0 && $num_photos === 0)
                <div class="flex w-full h-full items-center justify-center text-xl text-text-main-400">
                    <span class="block">
                    {{ $this->noImagesAlbumsMessage }}
                    </span>
                </div>
            @endif
            @if ($num_albums > 0 && $num_photos > 0)
                <x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />
            @endif
            @if ($num_albums > 0)
                @foreach ($this->albums as $data)
                    <x-gallery.album.thumbs.album :data="$data" :str-aspect-ratio-class="$this->flags->album_thumb_css_aspect_ratio" :cover-id="$this->flags->cover_id" />
                @endforeach
            @endif
            @if ($num_albums > 0 && $num_photos > 0)
                <x-gallery.divider title="{{ __('lychee.PHOTOS') }}" />
            @endif
            <x-gallery.view.photo-listing />
            @if ($this->flags->is_base_album)
                <livewire:pages.gallery.sensitive-warning :album="$this->album" />
            @endif
        </div>
        <x-gallery.view.photo />
        <x-gallery.album.sharing-links :album="$this->album" x-show="albumFlags.isSharingLinksOpen" />
    @endif
    @auth
        <livewire:modules.jobs.feedback />
    @endauth
</div>
