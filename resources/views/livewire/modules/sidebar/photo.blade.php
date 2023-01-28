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
				<x-atoms.line head='{{ __("lychee.PHOTO_TITLE") }}'>
					{{ $title }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_UPLOADED") }}'>
					{{ $created_at }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_DESCRIPTION") }}'>
					{{ $description }}
				</x-atoms.line>
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ $is_video ? __("lychee.PHOTO_VIDEO") : __("lychee.PHOTO_IMAGE") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_SIZE") }}'>
					{{ $filesize }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_FORMAT") }}'>
					{{ $type }}
				</x-atoms.line>
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
				<x-atoms.line head='{{ __("lychee.PHOTO_TAGS") }}'>
					{{ $tags }}
				</x-atoms.line>
			</tbody>
		</table>
		@if ($has_exif)
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_CAMERA") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.PHOTO_CAPTURED") }}'>
					{{ $taken_at }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_MAKE") }}'>
					{{ $make }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_TYPE") }}'>
					{{ $model }}
				</x-atoms.line>
				@if (!$is_video)
				<x-atoms.line head='{{ __("lychee.PHOTO_LENS") }}'>
					{{ $lens }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_SHUTTER") }}'>
					{{ $shutter }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_APERTURE") }}'>
					{{ "ƒ / " . $aperture }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_FOCAL") }}'>
					{{ $focal }}
				</x-atoms.line>
				{{-- TODO remove sprintf after ISO doesn't use placeholder anymore --}}
				<x-atoms.line head='{{ sprintf(__("lychee.PHOTO_ISO"), "") }}'>
					{{ $iso }}
				</x-atoms.line>
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
				<x-atoms.line head='{{ __("lychee.PHOTO_LATITUDE") }}'>
					{{ $latitude }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_LONGITUDE") }}'>
					{{ $longitude }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.PHOTO_ALTITUDE") }}'>
					{{ $altitude }}
				</x-atoms.line>
				@if ($location != null)
				<x-atoms.line head='{{ __("lychee.PHOTO_LOCATION") }}'>
					{{ $location }}
				</x-atoms.line>
				@endif
				@if ($img_direction != null)
				<x-atoms.line head='{{ __("lychee.PHOTO_IMGDIRECTION") }}'>
					{{ $img_direction }}
				</x-atoms.line>
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