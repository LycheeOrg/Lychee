<div id="lychee_sidebar" class="vflex-container">
	<div id="lychee_sidebar_header" class="vflex-item-rigid">
		<h1>{{ __("lychee.ALBUM_ABOUT") }}</h1>
	</div>
	<div id="lychee_sidebar_content" class="vflex-item-stretch">
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_BASICS") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_TITLE") }}' :value='$title' />
				<x-atoms.line head='{{ __("lychee.PHOTO_UPLOADED") }}' :value='$created_at' />
				<x-atoms.line head='{{ __("lychee.PHOTO_DESCRIPTION") }}' :value='$description' />
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ __($lychee.is_video ? "PHOTO_VIDEO" : "PHOTO_IMAGE") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_SIZE") }}' :value='$filesize' />
				<x-atoms.line head='{{ __("lychee.PHOTO_FORMAT") }}' :value='$type' />
				<x-atoms.line-skip head='{{ __("lychee.PHOTO_RESOLUTION") }}' :value='$resolution' />
				@if ($is_video)
				<x-atoms.line-skip head='{{ __("lychee.PHOTO_DURATION") }}' :value='$duration' />
				<x-atoms.line-skip head='{{ __("lychee.PHOTO_FPS") }}' :value='$fps' />
				@endif
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_TAGS") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_TAGS") }}' :value='$tags' />
			</tbody>
		</table>
		@if ($has_exif)
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_CAMERA") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_CAPTURED") }}' :value='$taken_at' />
				<x-atoms.line head='{{ __("lychee.PHOTO_MAKE") }}' :value='$make' />
				<x-atoms.line head='{{ __("lychee.PHOTO_TYPE") }}' :value='$model' />
				@if (!$is_video)
				<x-atoms.line head='{{ __("lychee.PHOTO_LENS") }}' :value='$lens' />
				<x-atoms.line head='{{ __("lychee.PHOTO_SHUTTER") }}' :value='$shutter' />
				<x-atoms.line head='{{ __("lychee.PHOTO_APERTURE") }}' :value='"ƒ / ".$aperture' />
				<x-atoms.line head='{{ __("lychee.PHOTO_FOCAL") }}' :value='$focal' />
				{{-- TODO remove sprintf after ISO doesn't use placeholder anymore --}}
				<x-atoms.line head='{{ sprintf(__("lychee.PHOTO_ISO"), "") }}' :value='$iso' />
				@endif
			</tbody>
		</table>
		@endif
		@if ($has_location)
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_LOCATION") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_LATITUDE") }}' :value='$latitude' />
				<x-atoms.line head='{{ __("lychee.PHOTO_LONGITUDE") }}' :value='$longitude' />
				<x-atoms.line head='{{ __("lychee.PHOTO_ALTITUDE") }}' :value='$altitude' />
				@if ($location != null)
				<x-atoms.line head='{{ __("lychee.PHOTO_LOCATION") }}' :value='$location' />
				@endif
				@if ($img_direction != null)
				<x-atoms.line head='{{ __("lychee.PHOTO_IMGDIRECTION") }}' :value='$img_direction' />
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