<div id="lychee_sidebar" class="vflex-container">
	<div id="lychee_sidebar_header" class="vflex-item-rigid">
		<h1>{{ Lang::get("ALBUM_ABOUT") }}</h1>
	</div>
	<div id="lychee_sidebar_content" class="vflex-item-stretch">
		<div class="sidebar__divider">
			<h1>{{ Lang::get("PHOTO_BASICS") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ Lang::get("PHOTO_TITLE") }}' :value='$title' />
				<x-atoms.line head='{{ Lang::get("PHOTO_UPLOADED") }}' :value='$created_at' />
				<x-atoms.line head='{{ Lang::get("PHOTO_DESCRIPTION") }}' :value='$description' />
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ Lang::get($is_video ? "PHOTO_VIDEO" : "PHOTO_IMAGE") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ Lang::get("PHOTO_SIZE") }}' :value='$filesize' />
				<x-atoms.line head='{{ Lang::get("PHOTO_FORMAT") }}' :value='$type' />
				<x-atoms.line-skip head='{{ Lang::get("PHOTO_RESOLUTION") }}' :value='$resolution' />
				@if ($is_video)
				<x-atoms.line-skip head='{{ Lang::get("PHOTO_DURATION") }}' :value='$duration' />
				<x-atoms.line-skip head='{{ Lang::get("PHOTO_FPS") }}' :value='$fps' />
				@endif
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ Lang::get("PHOTO_TAGS") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ Lang::get("PHOTO_TAGS") }}' :value='$tags' />
			</tbody>
		</table>
		@if ($has_exif)
		<div class="sidebar__divider">
			<h1>{{ Lang::get("PHOTO_CAMERA") }}</h1>
		</div>
		<table>
			<tbody>
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
			</tbody>
		</table>
		@endif
		@if ($has_location)
		<div class="sidebar__divider">
			<h1>{{ Lang::get("PHOTO_LOCATION") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ Lang::get("PHOTO_LATITUDE") }}' :value='$latitude' />
				<x-atoms.line head='{{ Lang::get("PHOTO_LONGITUDE") }}' :value='$longitude' />
				<x-atoms.line head='{{ Lang::get("PHOTO_ALTITUDE") }}' :value='$altitude' />
				@if ($location != null)
				<x-atoms.line head='{{ Lang::get("PHOTO_LOCATION") }}' :value='$location' />
				@endif
				@if ($img_direction != null)
				<x-atoms.line head='{{ Lang::get("PHOTO_IMGDIRECTION") }}' :value='$img_direction' />
				@endif
			</tbody>
		</table>
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
</div>