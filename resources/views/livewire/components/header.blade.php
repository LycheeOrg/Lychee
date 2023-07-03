<header
	id="lychee_toolbar_container"
	class="h-14 w-full flex-none bg-gradient-to-b from-dark-700 to-dark-800 border-b border-b-solid border-b-neutral-900 "
	{{-- @class([
		"",
		"hidden" => $is_hidden
	])> --}}
	@if ($gallery_mode === App\Enum\Livewire\GalleryMode::ALBUMS) <!-- ALBUMS -->
		@guest <!-- NOT LOGGED -->
		<x-header.bar>
			<x-header.button action="openLoginModal" icon="account-login" class="button__login" />
			<x-header.title>{{ $title }}</x-header.title>
			{{-- <x-header.search /> --}}
			{{-- <a class="button button--map-albums"><x-icons.iconic icon="map" /></a> --}}
		</x-header.bar>
		@endguest
		@auth
		<x-header.bar>
			<x-header.button action="openLeftMenu" icon="cog" class="button_settings" />
			<x-header.title>{{ $title }}</x-header.title>
			{{-- <x-header.search /> --}}
			{{-- <a class="header__divider"></a> --}}
			{{-- <a class="button button--map-albums"><x-icons.iconic icon="map" /></a> --}}
			@can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, null])
				<a class="button__add" wire:click="openContextMenu"><x-icons.iconic class="inline w-4 h-4 mr-0 ml-0" icon="plus" /></a>
			@endcan
		</x-header.bar>
		@endauth
	@elseif ($gallery_mode === App\Enum\Livewire\GalleryMode::ALBUM) <!-- ALBUM -->
		<x-header.bar>
			<x-header.button action="back" icon="chevron-left" class="button__back" />
			<x-header.title>{{ $title }}</x-header.title>
			{{-- @can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class], $this->album)
			<a class="button button--eye" id="button_visibility_album"><x-icons.iconic class="iconic--eye" icon="eye" /></a>
			@endcan
			@can(App\Policies\AlbumPolicy::CAN_SHARE_WITH_USERS, [App\Contracts\Models\AbstractAlbum::class, $this->album])
			<a class="button" id="button_sharing_album_users"><x-icons.iconic icon="people" /></a>
			@endcan
			@can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
			<a class="button button--nsfw" id="button_nsfw_album"><x-icons.iconic icon="warning" /></a>
			@endcan
			<a class="button button--share" id="button_share_album"><x-icons.iconic class="ionicons" icon="share-ion" /></a>
			@can(App\Policies\AlbumPolicy::CAN_DOWNLOAD, [App\Contracts\Models\AbstractAlbum::class, $this->album])
			<a class="button" id="button_archive"><x-icons.iconic icon="cloud-download" /></a>
			@endcan
			@if($baseAlbum !== null)
			<a wire:click='toggleSideBar' class="button button--info" id="button_info_album"><x-icons.iconic icon="info" /></a>
			@endif
			<a class="button button--map" id="button_map_album"><x-icons.iconic icon="map" /></a>
			@can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album])
			<a class="button" id="button_move_album"><x-icons.iconic icon="folder" /></a>
			<a class="button" id="button_trash_album"><x-icons.iconic icon="trash" /></a>
			@endcan --}}
			{{-- <a class="button" id="button_fs_album_enter"><x-icons.iconic icon="fullscreen-enter" /></a>
			<a class="button" id="button_fs_album_exit"><x-icons.iconic icon="fullscreen-exit" /></a> --}}
			{{-- <a class="header__divider"></a> --}}
			<a class="button__add" wire:click="openContextMenu"><x-icons.iconic class="inline w-4 h-4 mr-0 ml-0" icon="plus" /></a>
		</x-header.bar>
	@elseif ($gallery_mode === App\Enum\Livewire\GalleryMode::PHOTO) <!-- PHOTO -->
		<x-header.bar>
			<x-header.button action="back" icon="chevron-left" class="button__back" />
			<x-header.title>{{ $title }}</x-header.title>
			{{-- <a class="button button--star" id="button_star"><x-icons.iconic icon="star" /></a>
			<a class="button button--eye" id="button_visibility"><x-icons.iconic icon="eye" /></a>
			<a class="button button--rotate" id="button_rotate_ccwise"><x-icons.iconic icon="counterclockwise" /></a>
			<a class="button button--rotate" id="button_rotate_cwise"><x-icons.iconic icon="clockwise" /></a>
			<a class="button button--share" id="button_share"><x-icons.iconic class='ionicons' icon="share-ion" /></a>
			<a wire:click='toggleSideBar' class="button button--info" id="button_info"><x-icons.iconic icon="info" /></a>
			<a class="button button--map" id="button_map"><x-icons.iconic icon="map" /></a>
			<a class="button" id="button_move"><x-icons.iconic icon="folder" /></a>
			<a class="button" id="button_trash"><x-icons.iconic icon="trash" /></a>
			<a class="button" id="button_fs_enter"><x-icons.iconic icon="fullscreen-enter" /></a>
			<a class="button" id="button_fs_exit"><x-icons.iconic icon="fullscreen-exit" /></a>
			<a class="header__divider"></a> --}}
			<a class="button" wire:click="openContextMenu" id="button_more"><x-icons.iconic icon="ellipses" /></a>
		</x-header.bar>
	@elseif ($gallery_mode === App\Enum\Livewire\GalleryMode::MAP) <!-- MAP -->
		<x-header.bar>
			<x-header.button action="back" icon="chevron-left" class="button__back" />
			<x-header.title>{{ $title }}</x-header.title>
		</x-header.bar>
	@else
		<x-header.bar>
			<x-header.button action="back" icon="chevron-left" class="button__back" />
			<x-header.title>{{ $title }}</x-header.title>
		</x-header.bar>
	@endif
</header>