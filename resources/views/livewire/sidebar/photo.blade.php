<div class="sidebar__header">
	<h1>{{ Lang::get("ALBUM_ABOUT") }}</h1>
</div>
<div class="sidebar__wrapper">
<x-atoms.section head='{{ Lang::get("PHOTO_BASICS") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_TITLE") }}' :value='$title' />
	<x-atoms.line head='{{ Lang::get("PHOTO_UPLOADED") }}' :value='$uploaded' />
	<x-atoms.line head='{{ Lang::get("PHOTO_DESCRIPTION") }}' :value='$description' />
</x-atoms.section>

<x-atoms.section head='{{ Lang::get($isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_SIZE") }}' :value='$size' />
	<x-atoms.line head='{{ Lang::get("PHOTO_FORMAT") }}' :value='$type' />
	<x-atoms.line-skip head='{{ Lang::get("PHOTO_RESOLUTION") }}' :value='$resolution' />

	@if ($isVideo)
		<x-atoms.line-skip head='{{ Lang::get("PHOTO_DURATION") }}' :value='$duration' />
		<x-atoms.line-skip head='{{ Lang::get("PHOTO_FPS") }}' :value='$fps' />
	@endif
</x-atoms.section>

<x-atoms.section head='{{ Lang::get("PHOTO_TAGS") }}' >
	<x-atoms.line head='{{ Lang::get("ALBUM_IMAGES") }}' :value='$tags' />
</x-atoms.section>

@if(AccessControl::is_logged_in())
<x-atoms.section head='{{ Lang::get("ALBUM_SHARING") }}' >
	<x-atoms.line valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
	head='{{ Lang::get("ALBUM_PUBLIC") }}' :value='$is_public' />
	<x-atoms.line valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
	head='{{ Lang::get("ALBUM_HIDDEN") }}' :value='$requires_link' />
	<x-atoms.line valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
	head='{{ Lang::get("ALBUM_DOWNLOADABLE") }}' :value='$is_downloadable' />
	<x-atoms.line valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
	head='{{ Lang::get("ALBUM_SHARE_BUTTON_VISIBLE") }}' :value='$is_share_button_visible' />
	<x-atoms.line valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
	head='{{ Lang::get("ALBUM_PASSWORD") }}' :value='$has_password' />
	<x-atoms.line head='{{ Lang::get("ALBUM_OWNER") }}' :value='$owner_name' />
</x-atoms.section>
@endif
</div>
