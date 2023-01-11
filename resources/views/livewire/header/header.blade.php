<header id="lychee_toolbar_container" class="vflex-item-rigid">
	@if <!-- ALBUMS -->
		@if <!-- NOT LOGGED -->
		<div id="lychee_toolbar_public" class="toolbar">
			<a class="button" id="button_signin"><svg class="iconic"><use xlink:href="#account-login" /></svg></a>
			<a class="header__title"></a>
			<div class="header__search__field">
				<input class="header__search" type="text" name="search">
				<a class="header__clear">&times;</a>
			</div>
			<a class="button button--map-albums"><svg class="iconic"><use xlink:href="#map" /></svg></a>
		</div>
		@else <!-- LOGGED -->
		<div id="lychee_toolbar_albums" class="toolbar">
			<a class="button" id="button_settings"><svg class="iconic"><use xlink:href="#cog" /></svg></a>
			<a class="header__title"></a>
			<div class="header__search__field">
				<input class="header__search" type="text" name="search">
				<a class="header__clear">&times;</a>
			</div>
			<a class="header__divider"></a>
			<a class="button button--map-albums"><svg class="iconic"><use xlink:href="#map" /></svg></a>
			@can()
			<a class="button button_add"><svg class="iconic"><use xlink:href="#plus" /></svg></a>
			@endcan
		</div>
		@endif
	@elseif <!-- ALBUM -->
	<div id="lychee_toolbar_album" class="toolbar">
		<a class="button" id="button_back_home"><svg class="iconic"><use xlink:href="#chevron-left" /></svg></a>
		<a class="header__title"></a>
		<a class="button button--eye" id="button_visibility_album"><svg class="iconic iconic--eye"><use xlink:href="#eye" /></svg></a>
		@can(\App\Policies\AlbumPolicy::CAN_SHARE_WITH_USERS, [\App\Contracts\AbsractAlbum::class, $album])
		<a class="button" id="button_sharing_album_users"><svg class="iconic"><use xlink:href="#people" /></svg></a>
		@endcan
		@can(\App\Policies\AlbumPolicy::CAN_EDIT, [\App\Contracts\AbsractAlbum::class, $album])
		<a class="button button--nsfw" id="button_nsfw_album"><svg class="iconic"><use xlink:href="#warning" /></svg></a>
		@endcan
		<a class="button button--share" id="button_share_album"><svg class="iconic ionicons"><use xlink:href="#share-ion" /></svg></a>
		@can(\App\Policies\AlbumPolicy::CAN_DOWNLOAD, [\App\Contracts\AbsractAlbum::class, $album])
		<a class="button" id="button_archive"><svg class="iconic"><use xlink:href="#cloud-download" /></svg></a>
		@endcan
		<a class="button button--info" id="button_info_album"><svg class="iconic"><use xlink:href="#info" /></svg></a>
		<a class="button button--map" id="button_map_album"><svg class="iconic"><use xlink:href="#map" /></svg></a>
		@can(\App\Policies\AlbumPolicy::CAN_EDIT, [\App\Contracts\AbsractAlbum::class, $album])
		<a class="button" id="button_move_album"><svg class="iconic"><use xlink:href="#folder" /></svg></a>
		<a class="button" id="button_trash_album"><svg class="iconic"><use xlink:href="#trash" /></svg></a>
		@endcan
		<a class="button" id="button_fs_album_enter"><svg class="iconic"><use xlink:href="#fullscreen-enter" /></svg></a>
		<a class="button" id="button_fs_album_exit"><svg class="iconic"><use xlink:href="#fullscreen-exit" /></svg></a>
		<a class="header__divider"></a>
		<a class="button button_add"><svg class="iconic"><use xlink:href="#plus" /></svg></a>
	</div>
	@elseif <!-- PHOTO -->
	<div id="lychee_toolbar_photo" class="toolbar">
		<a class="button" id="button_back"><svg class="iconic"><use xlink:href="#chevron-left" /></svg></a>
		<a class="header__title"></a>
		<a class="button button--star" id="button_star"><svg class="iconic"><use xlink:href="#star" /></svg></a>
		<a class="button button--eye" id="button_visibility"><svg class="iconic"><use xlink:href="#eye" /></svg></a>
		<a class="button button--rotate" id="button_rotate_ccwise"><svg class="iconic"><use xlink:href="#counterclockwise" /></svg></a>
		<a class="button button--rotate" id="button_rotate_cwise"><svg class="iconic"><use xlink:href="#clockwise" /></svg></a>
		<a class="button button--share" id="button_share"><svg class="iconic ionicons"><use xlink:href="#share-ion" /></svg></a>
		<a class="button button--info" id="button_info"><svg class="iconic"><use xlink:href="#info" /></svg></a>
		<a class="button button--map" id="button_map"><svg class="iconic"><use xlink:href="#map" /></svg></a>
		<a class="button" id="button_move"><svg class="iconic"><use xlink:href="#folder" /></svg></a>
		<a class="button" id="button_trash"><svg class="iconic"><use xlink:href="#trash" /></svg></a>
		<a class="button" id="button_fs_enter"><svg class="iconic"><use xlink:href="#fullscreen-enter" /></svg></a>
		<a class="button" id="button_fs_exit"><svg class="iconic"><use xlink:href="#fullscreen-exit" /></svg></a>
		<a class="header__divider"></a>
		<a class="button" id="button_more"><svg class="iconic"><use xlink:href="#ellipses" /></svg></a>
	</div>
	@elseif <!-- MAP -->
	<div id="lychee_toolbar_map" class="toolbar">
		<a class="button" id="button_back_map"><svg class="iconic"><use xlink:href="#chevron-left" /></svg></a>
		<a class="header__title"></a>
	</div>
	@elseif <!-- SETTINGS -->
	<div id="lychee_toolbar_config" class="toolbar">
		<a class="button" id="button_close_config"><svg class="iconic"><use xlink:href="#plus" /></svg></a>
		<a class="header__title"></a>
	</div>
	@endif
</header>