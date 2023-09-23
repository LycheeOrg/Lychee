<div class="w-full"
    x-data="albumView(
        @entangle('sessionFlags.nsfwAlbumsVisible'),
        @entangle('sessionFlags.is_fullscreen'),
        @entangle('flags.can_edit'),
        @js(null),
        @entangle('albumIDs')
        )"
    @keydown.window="handleKeydown(event)"
    x-on:login-close="loginModalOpen = false">
    <!-- toolbar -->
    <x-header.bar>
        @guest
            <!-- NOT LOGGED -->
            <x-header.button x-on:click="loginModalOpen = true" icon="account-login" />
        @endguest
        @auth
            <x-header.button x-on:click="$dispatch('toggleLeftMenu')" icon="cog" />
        @endauth
        <x-header.title>{{ $title }}</x-header.title>
        <x-header.search />
        <x-header.actions-menus />
    </x-header.bar>
    <!-- albums -->
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)] flex flex-col">
        @if ($this->smartAlbums->isEmpty() && $this->albums->isEmpty() && $this->sharedAlbums->isEmpty())
            <div class="h-full flex flex-col justify-center"  x-init='loginModalOpen = true'>
                <div class="w-full text-center"><x-icons.iconic icon="eye" /></div>
                <p class="w-full text-center text-neutral-400">{{ __('lychee.VIEW_NO_PUBLIC_ALBUMS') }}</p>
            </div>
        @else
            <div class="flex flex-wrap flex-row flex-shrink w-full justify-start align-top">
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
    @if($flags->can_use_2fa)
    <x-webauthn.login />
    @endif
</div>
