<div class="header__toolbar header__toolbar--album header__toolbar--visible">

	<a wire:click="$emit('back')" class="button" id="button_back_home" title="{{ Lang::get('CLOSE_ALBUM') }}" data-tabindex="{{ Helpers::data_index_r() }}">
		<x-iconic icon="chevron-left" />
	</a>

	<a class="header__title" data-tabindex="{{ Helpers::data_index() }}">{{ $title }}</a>

	@if(AccessControl::is_logged_in())
	<a class="button button--eye" id="button_visibility_album" title="{{ Lang::get('VISIBILITY_ALBUM') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic class="iconic--eye" icon="eye" />
	</a>
	<a class="button" id="button_sharing_album_users" title="{{ Lang::get('SHARING_ALBUM_USERS') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="people" />
	</a>
	<a class="button button--nsfw" id="button_nsfw_album" title="{{ Lang::get('ALBUM_MARK_NSFW') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="warning" />
	</a>
	@endif
	<a class="button button--share" id="button_share_album" title="{{ Lang::get('SHARE_ALBUM') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic class="ionicons" icon="share-ion" />
	</a>
	<a class="button" id="button_archive" title="{{ Lang::get('DOWNLOAD_ALBUM') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="cloud-download" />
	</a>
	<a class="button button--info" id="button_info_album" title="{{ Lang::get('ABOUT_ALBUM') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="info" />
	</a>
	<a class="button button--map" id="button_map_album" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="map" />
	</a>
	@if(AccessControl::is_logged_in())
	<a class="button" id="button_move_album" title="{{ Lang::get('MOVE_ALBUM') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="folder" />
	</a>
	<a class="button" id="button_trash_album" title="{{ Lang::get('DELETE_ALBUM') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="trash" />
	</a>
	@endif
	<a class="button" id="button_fs_album_enter" title="{{ Lang::get('FULLSCREEN_ENTER') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="fullscreen-enter" />
	</a>
	<a class="button" id="button_fs_album_exit" title="{{ Lang::get('FULLSCREEN_EXIT') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="fullscreen-exit" />
	</a>
	<a class="header__divider"></a>
	@if(AccessControl::is_logged_in())
	<a class="button button_add" title="{{ Lang::get('ADD') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="plus" />
	</a>
	@endif
</div>