<div class="w-full">
    <!-- toolbar -->
    <x-header.bar>
        @guest
            <!-- NOT LOGGED -->
            <x-header.button 
                @keydown.window="if (event.keyCode === 76) { $wire.openLoginModal() }"
                wire:click="openLoginModal" icon="account-login"
                {{--  76 = l --}}
                />
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
                <div wire:init='openLoginModal'>
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
</div>
