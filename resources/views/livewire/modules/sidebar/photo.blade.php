<div id="lychee_sidebar" class="vflex-container">
	<div id="lychee_sidebar_header" class="vflex-item-rigid">
		<h1>{{ __("lychee.ALBUM_ABOUT") }}</h1>
	</div>
	<div id="lychee_sidebar_content" class="vflex-item-stretch">
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_BASICS") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-sidebar.line head='{{ __("lychee.PHOTO_TITLE") }}'>
					{{ $title }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_UPLOADED") }}'>
					{{ $created_at }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_DESCRIPTION") }}'>
					{{ $description }}
				</x-sidebar.line>
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ $is_video ? __("lychee.PHOTO_VIDEO") : __("lychee.PHOTO_IMAGE") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-sidebar.line head='{{ __("lychee.PHOTO_SIZE") }}'>
					{{ $filesize }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_FORMAT") }}'>
					{{ $type }}
				</x-sidebar.line>
				<x-sidebar.line-skip head='{{ __("lychee.PHOTO_RESOLUTION") }}' :value='$resolution' />
				@if ($is_video)
				<x-sidebar.line-skip head='{{ __("lychee.PHOTO_DURATION") }}' :value='$duration' />
				<x-sidebar.line-skip head='{{ __("lychee.PHOTO_FPS") }}' :value='$fps' />
				@endif
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_TAGS") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-sidebar.line head='{{ __("lychee.PHOTO_TAGS") }}'>
					{{ $tags }}
				</x-sidebar.line>
			</tbody>
		</table>
		@if ($has_exif)
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_CAMERA") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-sidebar.line head='{{ __("lychee.PHOTO_CAPTURED") }}'>
					{{ $taken_at }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_MAKE") }}'>
					{{ $make }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_TYPE") }}'>
					{{ $model }}
				</x-sidebar.line>
				@if (!$is_video)
				<x-sidebar.line head='{{ __("lychee.PHOTO_LENS") }}'>
					{{ $lens }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_SHUTTER") }}'>
					{{ $shutter }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_APERTURE") }}'>
					{{ "ƒ / " . $aperture }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_FOCAL") }}'>
					{{ $focal }}
				</x-sidebar.line>
				{{-- TODO remove sprintf after ISO doesn't use placeholder anymore --}}
				<x-sidebar.line head='{{ sprintf(__("lychee.PHOTO_ISO"), "") }}'>
					{{ $iso }}
				</x-sidebar.line>
				@endif
			</tbody>
		</table>
		@endif
		@if ($has_location)
		<div class="sidebar__divider">
			<h1>{{ __("lychee.PHOTO_LOCATION") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-sidebar.line head='{{ __("lychee.PHOTO_LATITUDE") }}'>
					{{ $latitude }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_LONGITUDE") }}'>
					{{ $longitude }}
				</x-sidebar.line>
				<x-sidebar.line head='{{ __("lychee.PHOTO_ALTITUDE") }}'>
					{{ $altitude }}
				</x-sidebar.line>
				@if ($location != null)
				<x-sidebar.line head='{{ __("lychee.PHOTO_LOCATION") }}'>
					{{ $location }}
				</x-sidebar.line>
				@endif
				@if ($img_direction != null)
				<x-sidebar.line head='{{ __("lychee.PHOTO_IMGDIRECTION") }}'>
					{{ $img_direction }}
				</x-sidebar.line>
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