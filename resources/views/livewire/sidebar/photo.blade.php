{{-- <div class="sidebar__header">
	<h1>{{ Lang::get('ALBUM_ABOUT') }}</h1>
</div>
<div class="sidebar__wrapper">
<x-atoms.section head='{{ Lang::get('ALBUM_BASICS') }}' >
	<tbody>
	<x-atoms.line head='{{ Lang::get('ALBUM_TITLE') }}' :value='$title' />
	<x-atoms.line head='{{ Lang::get('ALBUM_DESCRIPTION') }}' :value='$description' />
	@if ($isTagAlbum)
	<x-atoms.line head='{{ Lang::get('ALBUM_SHOW_TAGS') }}' :value='$showtags' />
	@endif
</x-atoms.section>

<x-atoms.section head='{{ Lang::get('ALBUM_ALBUM') }}' >
	<x-atoms.line head='{{ Lang::get('ALBUM_CREATED') }}' :value='$created_at->format("F Y")]' />
	@if ($children_count > 0)
	<x-atoms.line head='{{ Lang::get('ALBUM_SUBALBUMS') }}' :value='$children_count' />
	@endif
	@if ($photo_count > 0)
	<x-atoms.line head='{{ Lang::get('ALBUM_IMAGES') }}' :value='$photo_count' />
	@endif
	@if ($video_count > 0)
	<x-atoms.line head='{{ Lang::get('ALBUM_VIDEOS') }}' :value='$video_count' />
	@endif
	@if ($photo_count > 0)
		<x-atoms.line head='{{ Lang::get('ALBUM_ORDERING') }}' :value='$sorting_col == '' ? Lang::get('DEFAULT') : ($sorting_col + ' ' + $sorting_order)' />
	@endif
</x-atoms.section>

@if(AccessControl::is_logged_in())
<x-atoms.section head='{{ Lang::get('ALBUM_SHARING') }}' >
	<x-atoms.line is_boolean='{{ true }}' value_false='{{ Lang::get('ALBUM_SHR_YES') }}' value_true='{{ Lang::get('ALBUM_SHR_NO') }}'
	head='{{ Lang::get('ALBUM_PUBLIC') }}' :value='$is_public' />
	<x-atoms.line is_boolean='{{ true }}' value_false='{{ Lang::get('ALBUM_SHR_YES') }}' value_true='{{ Lang::get('ALBUM_SHR_NO') }}'
	head='{{ Lang::get('ALBUM_HIDDEN') }}' :value='$is_hidden' />
	<x-atoms.line is_boolean='{{ true }}' value_false='{{ Lang::get('ALBUM_SHR_YES') }}' value_true='{{ Lang::get('ALBUM_SHR_NO') }}'
	head='{{ Lang::get('ALBUM_DOWNLOADABLE') }}' :value='$is_downloadable' />
	<x-atoms.line is_boolean='{{ true }}' value_false='{{ Lang::get('ALBUM_SHR_YES') }}' value_true='{{ Lang::get('ALBUM_SHR_NO') }}'
	head='{{ Lang::get('ALBUM_SHARE_BUTTON_VISIBLE') }}' :value='$is_share_button_visible' />
	<x-atoms.line is_boolean='{{ true }}' value_false='{{ Lang::get('ALBUM_SHR_YES') }}' value_true='{{ Lang::get('ALBUM_SHR_NO') }}'
	head='{{ Lang::get('ALBUM_PASSWORD') }}' :value='$has_password' />
	<x-atoms.line head='{{ Lang::get('ALBUM_OWNER') }}' :value='$owner_name' />
</x-atoms.section>
@endif

<x-atoms.section head='{{ Lang::get('ALBUM_REUSE') }}' >
	<x-atoms.line head='{{ Lang::get('ALBUM_IMAGES') }}' :value='$license' />
</x-atoms.section>
</div> --}}
