<div id="image_overlay" class="absolute bottom-7 left-7 text-white text-shadow" x-show="overlayType !== 'none'" x-cloak>
	<h1 class=" text-3xl">{{ $title }}</h1>
    <p class="mt-1 text-xl" x-show="overlayType === 'description'">
        {{ $description }}
    </p>
    <p class="mt-1 text-xl" x-show="overlayType === 'date'">
        @if($is_camera_date)
            <span><x-icons.iconic class="w-4 h-4 fill-white m-0 mr-1 -mt-1" icon='camera-slr' /></span> {{ $date }}
        @else
            {{ $date }}
        @endif
    </p>
    <p class="mt-1 text-xl" x-show="overlayType === 'exif'">
        @if ($is_video)
        {{ $duration }} at {{ $fps }} fps
        @endif
        @if (!$is_video && $shutter !== '')
        {{ $shutter }} at &fnof; / {{ $aperture }}, {{ sprintf(__('lychee.PHOTO_ISO'), $iso) }}
        <br>
        {{ $focal }} {{ $lens === '' ? '' : sprintf('(%s)', $lens) }}
        @endif
    </p>
</div>