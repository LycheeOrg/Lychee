<header class="header">
	@if(!AccessControl::is_logged_in())
    <div class="header__toolbar header__toolbar--public header__toolbar--visible">

        <a class="button" id="button_signin" title="{{ Lang::get('SIGN_IN') }}" data-tabindex="1">
            <svg class="iconic"><use xlink:href="#account-login"></use></svg>
        </a>

        <a class="header__title" data-tabindex="2">{{ $title }}</a>

        <input class="header__search" type="text" name="search" placeholder="{{ Lang::get('SEARCH') }}" data-tabindex="3">
        <a class="header__clear header__clear_public">&times;</a>
        <a class="button button--map-albums" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="4">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
{{--        <a class="header__hostedwith">{{ Lang::get('HOSTED_WITH_LYCHEE') }}</a>--}}
	</div>
	@else

	@if($mode == 'albums')
    <div class="header__toolbar header__toolbar--albums header__toolbar--visible">

        <a class="button" id="button_settings" title="{{ Lang::get('SETTINGS') }}" data-tabindex="1">
            <svg class="iconic"><use xlink:href="#cog"></use></svg>
        </a>

        <a class="header__title" data-tabindex="2">{{ $title }}</a>

        <input class="header__search" type="text" name="search" placeholder="{{ Lang::get('SEARCH') }}" data-tabindex="3">
        <a class="header__clear">&times;</a>
        <a class="header__divider"></a>
        <a class="button button--map-albums" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="4">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
        <a class="button button_add" title="{{ Lang::get('ADD') }}" data-tabindex="5">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>

	</div>
	@elseif( $mode == 'album')
    <div class="header__toolbar header__toolbar--album header__toolbar--visible">

        <a class="button" id="button_back_home" title="{{ Lang::get('CLOSE_ALBUM') }}" data-tabindex="1">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="2"></a>

        <a class="button button--eye" id="button_visibility_album" title="{{ Lang::get('VISIBILITY_ALBUM') }}" data-tabindex="3">
            <svg class="iconic iconic--eye"><use xlink:href="#eye"></use></svg>
        </a>
        <a class="button" title="{{ $locale['SHARING_ALBUM_USERS'] }}" data-tabindex="4">
            <svg class="iconic"><use xlink:href="#people"></use></svg>
        </a>
        <a class="button button--nsfw" id="button_nsfw_album" title="{{ Lang::get('ALBUM_MARK_NSFW') }}" data-tabindex="5">
            <svg class="iconic"><use xlink:href="#warning"></use></svg>
        </a>
        <a class="button button--share" id="button_share_album" title="{{ Lang::get('SHARE_ALBUM') }}" data-tabindex="6">
            <svg class="iconic ionicons"><use xlink:href="#share-ion"></use></svg>
        </a>
        <a class="button" id="button_archive" title="{{ Lang::get('DOWNLOAD_ALBUM') }}" data-tabindex="7">
            <svg class="iconic"><use xlink:href="#cloud-download"></use></svg>
        </a>
        <a class="button button--info" id="button_info_album" title="{{ Lang::get('ABOUT_ALBUM') }}" data-tabindex="8">
            <svg class="iconic"><use xlink:href="#info"></use></svg>
        </a>
        <a class="button button--map" id="button_map_album" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="9">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
        <a class="button" id="button_move_album" title="{{ Lang::get('MOVE_ALBUM') }}" data-tabindex="10">
            <svg class="iconic"><use xlink:href="#folder"></use></svg>
        </a>
        <a class="button" id="button_trash_album" title="{{ Lang::get('DELETE_ALBUM') }}" data-tabindex="11">
            <svg class="iconic"><use xlink:href="#trash"></use></svg>
        </a>
        <a class="button" id="button_fs_album_enter" title="{{ Lang::get('FULLSCREEN_ENTER') }}" data-tabindex="12">
            <svg class="iconic"><use xlink:href="#fullscreen-enter"></use></svg>
        </a>
        <a class="button" id="button_fs_album_exit" title="{{ Lang::get('FULLSCREEN_EXIT') }}" data-tabindex="13">
            <svg class="iconic"><use xlink:href="#fullscreen-exit"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button button_add" title="{{ Lang::get('ADD') }}" data-tabindex="14">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>

	</div>
	@elseif ($mode == 'photo')
    <div class="header__toolbar header__toolbar--photo header__toolbar--visible">

        <a class="button" id="button_back" title="{{ Lang::get('CLOSE_PHOTO') }}" data-tabindex="1">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="2"></a>

        <a class="button button--star" id="button_star" title="{{ Lang::get('STAR_PHOTO') }}" data-tabindex="3">
            <svg class="iconic"><use xlink:href="#star"></use></svg>
        </a>
        <a class="button button--eye" id="button_visibility" title="{{ Lang::get('VISIBILITY_PHOTO') }}" data-tabindex="4">
            <svg class="iconic"><use xlink:href="#eye"></use></svg>
        </a>
        <a class="button button--rotate" id="button_rotate_ccwise" title="{{ Lang::get('PHOTO_EDIT_ROTATECCWISE') }}" data-tabindex="5">
            <svg class="iconic"><use xlink:href="#counterclockwise"></use></svg>
        </a>
        <a class="button button--rotate" id="button_rotate_cwise" title="{{ Lang::get('PHOTO_EDIT_ROTATECWISE') }}" data-tabindex="6">
            <svg class="iconic"><use xlink:href="#clockwise"></use></svg>
        </a>
        <a class="button button--share" id="button_share" title="{{ Lang::get('SHARE_PHOTO') }}" data-tabindex="7">
            <svg class="iconic ionicons"><use xlink:href="#share-ion"></use></svg>
        </a>
        <a class="button button--info" id="button_info" title="{{ Lang::get('ABOUT_PHOTO') }}" data-tabindex="8">
            <svg class="iconic"><use xlink:href="#info"></use></svg>
        </a>
        <a class="button button--map" id="button_map" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="9">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
        <a class="button" id="button_move" title="{{ Lang::get('MOVE') }}" data-tabindex="10">
            <svg class="iconic"><use xlink:href="#folder"></use></svg>
        </a>
        <a class="button" id="button_trash" title="{{ Lang::get('DELETE') }}" data-tabindex="11">
            <svg class="iconic"><use xlink:href="#trash"></use></svg>
        </a>
        <a class="button" id="button_fs_enter" title="{{ Lang::get('FULLSCREEN_ENTER') }}" data-tabindex="12">
            <svg class="iconic"><use xlink:href="#fullscreen-enter"></use></svg>
        </a>
        <a class="button" id="button_fs_exit" title="{{ Lang::get('FULLSCREEN_EXIT') }}" data-tabindex="13">
            <svg class="iconic"><use xlink:href="#fullscreen-exit"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button" id="button_more" title="{{ Lang::get('MORE') }}" data-tabindex="14">
            <svg class="iconic"><use xlink:href="#ellipses"></use></svg>
        </a>

    </div>
	@elseif ($mode == 'map')
    <div class="header__toolbar header__toolbar--map">

        <a class="button" id="button_back_map" title="{{ Lang::get('CLOSE_MAP') }}" data-tabindex="1">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="2"></a>

	</div>
	@else 
    <div class="header__toolbar header__toolbar--map">

        <a class="button" id="button_back_map" title="{{ $mode }}" data-tabindex="1">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="2"></a>

	</div>
	@endif
	@endif

</header>
