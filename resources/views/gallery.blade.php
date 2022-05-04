@extends('layouts.gallery')

@section('head-js')
@endsection

@section('head-css')
<link type="text/css" rel="stylesheet" href="{{ Helpers::cacheBusting('dist/main.css') }}">
<link type="text/css" rel="stylesheet" href="{{ Helpers::cacheBusting('dist/user.css') }}">
@if (Helpers::getDeviceType()=="television")
<link type="text/css" rel="stylesheet" href="{{ Helpers::cacheBusting('dist/TV.css') }}">
@endif
@endsection

@section('content')
<div id="container">

@include('includes.svg')

<!-- Loading -->
<div id="loading"></div>

<!-- Header -->
<header class="header">
    <div class="header__toolbar header__toolbar--public">

        <a class="button" id="button_signin" title="{{ $locale['SIGN_IN'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
			<x-iconic icon='account-login' />
        </a>

        <a class="header__title" data-tabindex="{{ Helpers::data_index() }}"></a>

		<div class="header__search__field">
	        <input class="header__search" type="text" name="search" placeholder="{{ $locale['SEARCH'] }}" data-tabindex="{{ Helpers::data_index() }}">
    	    <a class="header__clear">&times;</a>
    	</div>
        <a class="button button--map-albums" title="{{ $locale['DISPLAY_FULL_MAP'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
{{--        <a class="header__hostedwith">{{ $locale['HOSTED_WITH_LYCHEE'] }}</a>--}}

    </div>
    <div class="header__toolbar header__toolbar--albums">

        <a class="button" id="button_settings" title="{{ $locale['SETTINGS'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
            <svg class="iconic"><use xlink:href="#cog"></use></svg>
        </a>

        <a class="header__title" data-tabindex="{{ Helpers::data_index() }}"></a>
		<div class="header__search__field">
        	<input class="header__search" type="text" name="search" placeholder="{{ $locale['SEARCH'] }}" data-tabindex="{{ Helpers::data_index() }}">
        	<a class="header__clear">&times;</a>
        </div>
        <a class="header__divider"></a>
        <a class="button button--map-albums" title="{{ $locale['DISPLAY_FULL_MAP'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
        <a class="button button_add" title="{{ $locale['ADD'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>

    </div>
    <div class="header__toolbar header__toolbar--album">

        <a class="button" id="button_back_home" title="{{ $locale['CLOSE_ALBUM'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="{{ Helpers::data_index() }}"></a>

        <a class="button button--eye" id="button_visibility_album" title="{{ $locale['VISIBILITY_ALBUM'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic iconic--eye"><use xlink:href="#eye"></use></svg>
        </a>
        <a class="button" id="button_sharing_album_users" title="{{ $locale['SHARING_ALBUM_USERS'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#people"></use></svg>
        </a>
        <a class="button button--nsfw" id="button_nsfw_album" title="{{ $locale['ALBUM_MARK_NSFW'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#warning"></use></svg>
        </a>
        <a class="button button--share" id="button_share_album" title="{{ $locale['SHARE_ALBUM'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic ionicons"><use xlink:href="#share-ion"></use></svg>
        </a>
        <a class="button" id="button_archive" title="{{ $locale['DOWNLOAD_ALBUM'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#cloud-download"></use></svg>
        </a>
        <a class="button button--info" id="button_info_album" title="{{ $locale['ABOUT_ALBUM'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#info"></use></svg>
        </a>
        <a class="button button--map" id="button_map_album" title="{{ $locale['DISPLAY_FULL_MAP'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
        <a class="button" id="button_move_album" title="{{ $locale['MOVE_ALBUM'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#folder"></use></svg>
        </a>
        <a class="button" id="button_trash_album" title="{{ $locale['DELETE_ALBUM'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#trash"></use></svg>
        </a>
        <a class="button" id="button_fs_album_enter" title="{{ $locale['FULLSCREEN_ENTER'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#fullscreen-enter"></use></svg>
        </a>
        <a class="button" id="button_fs_album_exit" title="{{ $locale['FULLSCREEN_EXIT'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#fullscreen-exit"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button button_add" title="{{ $locale['ADD'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>

    </div>
    <div class="header__toolbar header__toolbar--photo">

        <a class="button" id="button_back" title="{{ $locale['CLOSE_PHOTO'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="{{ Helpers::data_index() }}"></a>

        <a class="button button--star" id="button_star" title="{{ $locale['STAR_PHOTO'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#star"></use></svg>
        </a>
        <a class="button button--eye" id="button_visibility" title="{{ $locale['VISIBILITY_PHOTO'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#eye"></use></svg>
        </a>
        <a class="button button--rotate" id="button_rotate_ccwise" title="{{ $locale['PHOTO_EDIT_ROTATECCWISE'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#counterclockwise"></use></svg>
        </a>
        <a class="button button--rotate" id="button_rotate_cwise" title="{{ $locale['PHOTO_EDIT_ROTATECWISE'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#clockwise"></use></svg>
        </a>
        <a class="button button--share" id="button_share" title="{{ $locale['SHARE_PHOTO'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic ionicons"><use xlink:href="#share-ion"></use></svg>
        </a>
        <a class="button button--info" id="button_info" title="{{ $locale['ABOUT_PHOTO'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#info"></use></svg>
        </a>
        <a class="button button--map" id="button_map" title="{{ $locale['DISPLAY_FULL_MAP'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#map"></use></svg>
        </a>
        <a class="button" id="button_move" title="{{ $locale['MOVE'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#folder"></use></svg>
        </a>
        <a class="button" id="button_trash" title="{{ $locale['DELETE'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#trash"></use></svg>
        </a>
        <a class="button" id="button_fs_enter" title="{{ $locale['FULLSCREEN_ENTER'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#fullscreen-enter"></use></svg>
        </a>
        <a class="button" id="button_fs_exit" title="{{ $locale['FULLSCREEN_EXIT'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#fullscreen-exit"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button" id="button_more" title="{{ $locale['MORE'] }}" data-tabindex="{{ Helpers::data_index() }}">
            <svg class="iconic"><use xlink:href="#ellipses"></use></svg>
        </a>

    </div>

    <div class="header__toolbar header__toolbar--map">

        <a class="button" id="button_back_map" title="{{ $locale['CLOSE_MAP'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title" data-tabindex="{{ Helpers::data_index() }}"></a>

    </div>

	<div class="header__toolbar header__toolbar--config">
        <a class="button" id="button_close_config" title="{{ $locale['CLOSE'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>
        <a class="header__title" data-tabindex="{{ Helpers::data_index() }}"></a>
    </div>

</header>

<!-- leftMenu -->
<div class="leftMenu"></div>

<!-- Content -->
<div class="content"></div>

<!-- MapView -->
<div id="mapview">
  <div id="leaflet_map_full"></div>
</div>

<!-- ImageView -->
<div id="imageview"></div>

<!-- Warning -->
<div id="sensitive_warning">
	{!! App\Models\Configs::get_value('nsfw_warning_text','<h1>Sensitive content</h1><p>This album contains sensitive content which some people may find offensive or disturbing.</p><p>Tap to consent.</p>'); !!}
</div>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar__header">
        <h1>{{ $locale['PHOTO_ABOUT'] }}</h1>
    </div>
    <div class="sidebar__wrapper"></div>
</div>

<!-- Upload -->
<div id="upload">
    <input id="upload_files" type="file" name="fileElem[]" multiple accept="image/*,video/*,.mov">
</div>

<!-- Upload track -->
<div id="upload">
	<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml">
</div>

<!-- JS -->
<script async type="text/javascript" src="{{ Helpers::cacheBusting('dist/main.js') }}"></script>
</div>

@include('includes.footer')
@endsection
