<div id="lychee_sidebar" class="border-t border-solid border-sky-500 text-white w-[360px]">
	<div class="grid grid-cols-[auto minmax(0, 1fr)]">
		<h1 class="col-span-2 text-center text-lg font-bold my-4">{{ __("lychee.ALBUM_ABOUT") }}</h1>
		<h2 class="col-span-2 text-neutral-400 font-bold px-3 pt-4 pb-3">{{ __("lychee.PHOTO_BASICS") }}</h2>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_TITLE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $title }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_UPLOADED") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $created_at }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_DESCRIPTION") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $description }}</span>

		<h2 class="col-span-2 text-neutral-400 font-bold px-3 pt-4 pb-3">{{ $is_video ? __("lychee.PHOTO_VIDEO") : __("lychee.PHOTO_IMAGE") }}</h1>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_SIZE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $filesize }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_FORMAT") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $type }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_RESOLUTION") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $resolution }}</span>
		@if($is_video)
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_DURATION") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $duration }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_FPS") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $fps }}</span>
		@endif
		<h2 class="col-span-2 text-neutral-400 font-bold px-3 pt-4 pb-3">{{ __("lychee.PHOTO_TAGS") }}</h1>
		<span class="py-0.5 pl-3 col-span-2 text-sm">tags, tags, tags</span>
		@if ($has_exif)
		<h2 class="col-span-2 text-neutral-400 font-bold px-3 pt-4 pb-3">{{ __("lychee.PHOTO_CAMERA") }}</h1>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_CAPTURED") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $taken_at }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_MAKE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $make }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_TYPE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $model }}</span>
		@if (!$is_video)
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_LENS") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $lens }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_SHUTTER") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $shutter }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_APERTURE") }}</span>
		<span class="py-0.5 pl-0 text-sm">ƒ / {{ $aperture }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_FOCAL") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $focal }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ sprintf(__("lychee.PHOTO_ISO"), "") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $iso }}</span>
		@endif
		@endif
		@if ($has_location)
		<h2 class="col-span-2 text-neutral-400 font-bold px-3 pt-4 pb-3">{{ __("lychee.PHOTO_LOCATION") }}</h1>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_LATITUDE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $latitude }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_LONGITUDE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $longitude }}</span>
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_ALTITUDE") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $altitude }}</span>
		@if ($location != null)
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_LOCATION") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $location }}</span>
		@endif
		@if ($img_direction != null)
		<span class="py-0.5 pl-3 text-sm">{{ __("lychee.PHOTO_IMGDIRECTION") }}</span>
		<span class="py-0.5 pl-0 text-sm">{{ $img_direction }}</span>
		@endif
		@endif
	{{--
	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }],
	};
	--}}


	{{-- @if(Auth::check()) --}}
	{{-- structure.sharing = {
		title: lychee.locale["PHOTO_SHARING"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: isPublic }],
	};
	--}}
	{{-- @endif --}}
	</div>
</div>