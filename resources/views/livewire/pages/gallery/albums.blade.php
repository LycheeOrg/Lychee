<div class="w-full"
    x-data="albumView(
        @js(route('livewire-gallery')),
        @entangle('sessionFlags.nsfwAlbumsVisible'),
        @entangle('sessionFlags.is_fullscreen'),
        @entangle('sessionFlags.are_photo_details_open'),
        @js($rights),
        @js(null),
        @js($this->albumIDs),
        )"
    @keydown.window="handleKeydown(event)"
    x-on:login-close="loginModalOpen = false">
    <!-- toolbar -->
    <x-header.bar>
        @guest
            <x-backButtonHeader class="{{ $this->isLoginLeft ? 'order-4' : 'order-0' }}" />
            <!-- NOT LOGGED -->
            <x-header.button x-on:click="loginModalOpen = true" icon="account-login" class="{{ $this->isLoginLeft ? 'order-0' : 'order-4' }}" />
        @endguest
        @auth
            <x-header.button x-on:click="$dispatch('toggleLeftMenu')" icon="cog" />
        @endauth
        <x-header.title>{{ $title }}</x-header.title>
        @if($flags->is_search_accessible)
        <x-header.button class="flex flex-grow justify-end" href="{{ route('livewire-search') }}" wire:navigate icon="magnifying-glass" />
        @endif
        <x-header.actions-menus />
    </x-header.bar>
    <!-- albums -->
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)] flex flex-col">
        @if ($this->smartAlbums->isEmpty() && $this->albums->isEmpty() && $this->sharedAlbums->isEmpty())
            <div class="h-full flex flex-col justify-center"  x-init='loginModalOpen = true'>
                <div class="w-full text-center"><x-icons.iconic icon="eye" /></div>
                <p class="w-full text-center text-text-main-400">{{ __('lychee.VIEW_NO_PUBLIC_ALBUMS') }}</p>
            </div>
        @else
            <div class="flex flex-wrap flex-row flex-shrink w-full justify-start align-top">
                @if ($this->smartAlbums->count() > 0)
                    <x-gallery.divider title="{{ __('lychee.SMART_ALBUMS') }}" />
                    @foreach ($this->smartAlbums as $album)
                        <x-gallery.album.thumbs.album :data="$album" :str-aspect-ratio-class="$this->flags->album_thumb_css_aspect_ratio" :cover-id="null" />
                    @endforeach
                    @if ($this->albums->count() > 0)
                        <x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />
                    @endif
                @endif

                @if ($this->albums->count() > 0)
                    @foreach ($this->albums as $album)
                        <x-gallery.album.thumbs.album :data="$album" :str-aspect-ratio-class="$this->flags->album_thumb_css_aspect_ratio" :cover-id="null" />
                    @endforeach
                @endif

                @php
                    $oldUsername = '';
                @endphp
                @if ($this->sharedAlbums->count() > 0)
                    @foreach ($this->sharedAlbums as $album)
                        @if ($oldUsername !== $album->owner->username)
                        <x-gallery.divider :title="$album->owner->username" />
                        @php
                            $oldUsername = $album->owner->username;
                        @endphp
                        @endif
                        <x-gallery.album.thumbs.album :data="$album" :str-aspect-ratio-class="$flags->album_thumb_css_aspect_ratio" :cover-id="null" />
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
                opacity-100 bg-gradient-to-b from-bg-300 to-bg-400
                relative w-[500px] text-sm rounded-md text-text-main-400 animate-moveUp
                "
                role="dialog" x-on:click.away="loginModalOpen = false">
                <livewire:modals.login />
            </div>
        </div>
    @endguest
    @if($flags->can_use_2fa)
    <x-webauthn.login />
    @endif
    @auth
        <livewire:modules.jobs.feedback />
    @endauth
</div>
