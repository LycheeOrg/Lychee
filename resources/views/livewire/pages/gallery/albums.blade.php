<div class="w-full"
    x-data="{
        loginModalOpen:false,
        selectedPhotos: [],
        selectedAlbums: [],
        detailsOpen: false,
        sharingLinksOpen: false,
        nsfwAlbumsVisible: @entangle('sessionFlags.nsfwAlbumsVisible'),
        isFullscreen: @entangle('sessionFlags.is_fullscreen'),
        silentToggle(elem) {
            this[elem] = ! this[elem];
            $wire.silentUpdate();
        },
        handleContextPhoto(event) {
            this.selectedAlbums = [];
            const photoId = event.currentTarget.dataset.id;
            const index = this.selectedPhotos.indexOf(photoId);
            if (index > -1 && this.selectedPhotos.length > 1) { // found and more than one element
                $wire.openPhotosDropdown(event.clientX, event.clientY, this.selectedPhotos);
            } else {
                $wire.openPhotoDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
            }
        },
        handleClickPhoto(event) {
            if (event.ctrlKey) {
                event.preventDefault();
                this.selectedAlbums = [];
                const photoId = event.currentTarget.dataset.id;
                const index = this.selectedPhotos.indexOf(photoId);
                if (index > -1) { // found
                    this.selectedPhotos = this.selectedPhotos.filter((e) => e !== photoId);
                } else { // not found
                    this.selectedPhotos.push(photoId);
                }
            }
        },
        handleContextAlbum(event) {
            this.selectedPhotos = [];
            const albumId = event.currentTarget.dataset.id;
            const index = this.selectedAlbums.indexOf(albumId);
            if (index > -1 && this.selectedAlbums.length > 1) { // found and more than one element
                $wire.openAlbumsDropdown(event.clientX, event.clientY, this.selectedAlbums);
            } else {
                $wire.openAlbumDropdown(event.clientX, event.clientY, event.currentTarget.dataset.id);
            }
        },
        handleClickAlbum(event) {
            if (event.ctrlKey) {
                event.preventDefault();
                this.selectedPhotos = [];
                const albumId = event.currentTarget.dataset.id;
                const index = this.selectedAlbums.indexOf(albumId);
                if (index > -1) { // found
                    this.selectedAlbums = this.selectedAlbums.filter((e) => e !== albumId);
                } else { // not found
                    this.selectedAlbums.push(albumId);
                }
            }
        },
        handleKeydown(event) {
            if (event.keyCode === 72) { event.preventDefault(); console.log('toggle hidden albums:', this.nsfwAlbumsVisible); this.silentToggle('nsfwAlbumsVisible'); }
            if (event.keyCode === 70 && $focus.focused() === undefined) { event.preventDefault(); this.silentToggle('isFullscreen') }
        }
    }"
    {{-- 72 = h --}}
    {{-- 73 = i --}}
    {{-- 70 = f --}}
    @keydown.window="handleKeydown(event)"
    x-on:login-close="loginModalOpen = false">
    <!-- toolbar -->
    <x-header.bar>
        @guest
            <!-- NOT LOGGED -->
            <x-header.button @keydown.window="if (event.keyCode === 76) { loginModalOpen = true }" x-on:click="loginModalOpen = true" icon="account-login" {{--  76 = l --}} />
        @endguest
        @auth
            <x-header.button x-bind="leftMenuOpen" x-on:click="leftMenuOpen = ! leftMenuOpen" icon="cog" />
        @endauth
        <x-header.title>{{ $title }}</x-header.title>
        <x-header.search />
        {{-- <a class="button button--map-albums"><x-icons.iconic icon="map" /></a> --}}
        @can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, null])
            <x-header.button wire:click="openContextMenu" icon="plus" />
        @endcan
    </x-header.bar>
    <!-- albums -->
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
        @if ($this->smartAlbums->isEmpty() && $this->albums->isEmpty() && $this->sharedAlbums->isEmpty())
            <div>
                <div x-on:init='loginModalOpen = true'>
                    <x-icons.iconic icon="eye" />
                    <p>{{ __('lychee.VIEW_NO_PUBLIC_ALBUMS') }}</p>
                </div>
            </div>
        @else
            <div class="flex flex-wrap flex-auto flex-shrink-0 w-full justify-start">
                @if ($this->smartAlbums->count() > 0)
                    <x-gallery.divider title="{{ __('lychee.SMART_ALBUMS') }}" />
                    @foreach ($this->smartAlbums as $album)
                        <x-gallery.album.thumbs.album :data="$album" />
                    @endforeach
                    @if ($this->albums->count() > 0)
                        <x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />
                    @endif
                @endif

                @if ($this->albums->count() > 0)
                    @foreach ($this->albums as $album)
                        <x-gallery.album.thumbs.album :data="$album" />
                    @endforeach
                @endif

                @if ($this->sharedAlbums->count() > 0)
                    <x-gallery.divider title="{{ __('lychee.SHARED_ALBUMS') }}" />
                    @foreach ($this->sharedAlbums as $album)
                        <x-gallery.album.thumbs.album :data="$album" />
                    @endforeach
                @endif
            </div>
        @endif
        <x-footer />
    </div>
    @guest
        <div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
	bg-black/80 z-50 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
            data-closable="true" x-cloak x-show="loginModalOpen">
            <div class="basicModal transition-opacity ease-in duration-1000
                opacity-100 bg-gradient-to-b from-dark-300 to-dark-400
                relative w-[500px] text-sm rounded-md text-neutral-400 animate-moveUp
                "
                role="dialog" x-on:click.away="loginModalOpen = false">
                <livewire:modals.login />
            </div>
        </div>
    @endguest
    @if($can_use_2fa)
    <x-webauthn.login />
    @endif
</div>
