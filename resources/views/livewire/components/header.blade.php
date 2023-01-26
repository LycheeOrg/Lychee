<header
	id="lychee_toolbar_container"
	@class([
		"vflex-item-rigid",
		"hidden" => $is_hidden
	])>
	@if ($gallery_mode === App\Enum\Livewire\GalleryMode::ALBUMS) <!-- ALBUMS -->
		@if (Auth::user() === null) <!-- NOT LOGGED -->
		<div id="lychee_toolbar_public" class="toolbar visible">
			<a class="button" wire:click="openLoginModal" id="button_settings"><x-icons.iconic icon="account-login" /></a>
			<a class="header__title">{{ $title }}</a>
			<div class="header__search__field">
				<input class="header__search" type="text" name="search" placeholder="Search …">
				<a class="header__clear">&times;</a>
			</div>
			<a class="button button--map-albums"><x-icons.iconic icon="map" /></a>
		</div>
		@else <!-- LOGGED -->
		<div id="lychee_toolbar_albums" class="toolbar visible">
			<a class="button" wire:click="openLeftMenu" id="button_settings"><x-icons.iconic icon="cog" /></a>
			<a class="header__title">{{ $title }}</a>
			<div class="header__search__field">
				<input class="header__search" type="text" name="search" placeholder="Search …">
				<a class="header__clear">&times;</a>
			</div>
			<a class="header__divider"></a>
			{{-- <a class="button button--map-albums"><x-icons.iconic icon="map" /></a> --}}
			@can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, null])
				<a class="button button_add" wire:click="openContextMenu"><x-icons.iconic icon="plus" /></a>
			@endcan
		</div>
		@endif
	@elseif ($gallery_mode === App\Enum\Livewire\GalleryMode::ALBUM) <!-- ALBUM -->
		<div id="lychee_toolbar_album" class="toolbar visible">
			<a class="button" id="button_back_home" title="Close Album" wire:click="back"><x-icons.iconic icon="chevron-left" /></a>
			<a class="header__title">{{ $title }}</a>
			@can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class], $this->album)
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
			@endcan
			<a class="button" id="button_fs_album_enter"><x-icons.iconic icon="fullscreen-enter" /></a>
			<a class="button" id="button_fs_album_exit"><x-icons.iconic icon="fullscreen-exit" /></a>
			<a class="header__divider"></a>
			<a class="button button_add" wire:click="openContextMenu"><x-icons.iconic icon="plus" /></a>
		</div>
	@elseif ($gallery_mode === App\Enum\Livewire\GalleryMode::PHOTO) <!-- PHOTO -->
		<div id="lychee_toolbar_photo" class="toolbar visible">
			<a class="button" id="button_back" wire:click="back"><x-icons.iconic icon="chevron-left" /></a>
			<a class="header__title">{{ $title }}</a>
			<a class="button button--star" id="button_star"><x-icons.iconic icon="star" /></a>
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
			<a class="header__divider"></a>
			<a class="button" wire:click="openContextMenu" id="button_more"><x-icons.iconic icon="ellipses" /></a>
		</div>
	@elseif ($gallery_mode === App\Enum\Livewire\GalleryMode::MAP) <!-- MAP -->
		<div id="lychee_toolbar_map" class="toolbar visible">
			<a class="button" id="button_back_map"><x-icons.iconic icon="chevron-left" /></a>
			<a class="header__title"></a>
		</div>
	@else
		<div id="lychee_toolbar_config" class="toolbar visible">
			<a class="button" id="button_close_config" wire:click="back"><x-icons.iconic icon="chevron-left" /></a>
			<a class="header__title">{{ $title }}</a>
		</div>
	@endif
</header>