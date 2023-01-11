<div class="sidebar__header">
	<h1>{{ Lang::get("ALBUM_ABOUT") }}</h1>
</div>
<div class="sidebar__wrapper">
<x-atoms.section head='{{ Lang::get("PHOTO_BASICS") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_TITLE") }}' :value='$title' />
	<x-atoms.line head='{{ Lang::get("PHOTO_UPLOADED") }}' :value='$created_at' />
	<x-atoms.line head='{{ Lang::get("PHOTO_DESCRIPTION") }}' :value='$description' />
</x-atoms.section>

<x-atoms.section head='{{ Lang::get($is_video ? "PHOTO_VIDEO" : "PHOTO_IMAGE") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_SIZE") }}' :value='$filesize' />
	<x-atoms.line head='{{ Lang::get("PHOTO_FORMAT") }}' :value='$type' />
	<x-atoms.line-skip head='{{ Lang::get("PHOTO_RESOLUTION") }}' :value='$resolution' />

	@if ($is_video)
		<x-atoms.line-skip head='{{ Lang::get("PHOTO_DURATION") }}' :value='$duration' />
		<x-atoms.line-skip head='{{ Lang::get("PHOTO_FPS") }}' :value='$fps' />
	@endif
</x-atoms.section>

<x-atoms.section head='{{ Lang::get("PHOTO_TAGS") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_TAGS") }}' :value='$tags' />
</x-atoms.section>

@if ($has_exif)
<x-atoms.section head='{{ Lang::get("PHOTO_CAMERA") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_CAPTURED") }}' :value='$taken_at' />
	<x-atoms.line head='{{ Lang::get("PHOTO_MAKE") }}' :value='$make' />
	<x-atoms.line head='{{ Lang::get("PHOTO_TYPE") }}' :value='$model' />
	@if (!$is_video)
	<x-atoms.line head='{{ Lang::get("PHOTO_LENS") }}' :value='$lens' />
	<x-atoms.line head='{{ Lang::get("PHOTO_SHUTTER") }}' :value='$shutter' />
	<x-atoms.line head='{{ Lang::get("PHOTO_APERTURE") }}' :value='"ƒ / ".$aperture' />
	<x-atoms.line head='{{ Lang::get("PHOTO_FOCAL") }}' :value='$focal' />
	{{-- TODO remove sprintf after ISO doesn't use placeholder anymore --}}
	<x-atoms.line head='{{ sprintf(Lang::get("PHOTO_ISO"), "") }}' :value='$iso' />
	@endif
</x-atoms.section>
@endif

@if ($has_location)
<x-atoms.section head='{{ Lang::get("PHOTO_LOCATION") }}' >
	<x-atoms.line head='{{ Lang::get("PHOTO_LATITUDE") }}' :value='$latitude' />
	<x-atoms.line head='{{ Lang::get("PHOTO_LONGITUDE") }}' :value='$longitude' />
	<x-atoms.line head='{{ Lang::get("PHOTO_ALTITUDE") }}' :value='$altitude' />
	@if ($location != null)
		<x-atoms.line head='{{ Lang::get("PHOTO_LOCATION") }}' :value='$location' />
	@endif
	@if ($img_direction != null)
		<x-atoms.line head='{{ Lang::get("PHOTO_IMGDIRECTION") }}' :value='$img_direction' />
	@endif
</x-atoms.section>
@endif
{{--
structure.license = {
	title: lychee.locale["PHOTO_REUSE"],
	type: sidebar.types.DEFAULT,
	rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }],
};
--}}


@if(Auth::check())
{{-- structure.sharing = {
	title: lychee.locale["PHOTO_SHARING"],
	type: sidebar.types.DEFAULT,
	rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: isPublic }],
};
--}}
@endif
</div>
