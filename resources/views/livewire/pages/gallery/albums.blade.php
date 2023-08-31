<div class="w-full"
    x-data="{
        loginModalOpen:false,
        nsfwAlbumsVisible:{{ $nsfwAlbumsVisible ? 'true' : 'false' }}
    }"
    @keydown.window="if (event.keyCode === 72) { event.preventDefault(); nsfwAlbumsVisible = ! nsfwAlbumsVisible }"
    {{-- 72 = h --}}
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
