<div id="image_overlay" class="absolute bottom-7 left-7 text-text-main-0 text-shadow text-white"
    x-show="photoFlags.overlayType !== 'none'" x-cloak>
    <h1 class=" text-3xl" x-text="photo.title" ></h1>
    <p class="mt-1 text-xl" x-show="photoFlags.overlayType === 'desc'" x-text="photo.description" />
    <p class="mt-1 text-xl" x-show="photoFlags.overlayType === 'date'">
        <span x-show="photo.precomputed.is_camera_date">
            <x-icons.iconic class="w-4 h-4 fill-white m-0 mr-1 -mt-1" icon='camera-slr' /></span>
        <span x-text="photo.preformatted.date_overlay"></span>
    </p>
    <p class="mt-1 text-xl" x-show="photoFlags.overlayType === 'exif' && photo.precomputed.is_video">
        <span x-text="photo.preformatted.duration"></span> at <span x-text="photo.preformatted.fps"></span> fps
    </p>
    <p class="mt-1 text-xl"
        x-show="photoFlags.overlayType === 'exif' && !photo.precomputed.is_video && photo.preformatted.shutter !== ''">
        <span x-text="photo.preformatted.shutter"></span> at &fnof; / <span
            x-text="photo.preformatted.aperture"></span>, <span x-text="photo.preformatted.iso"></span>
        <br>
        <span x-text="photo.focal"></span> <span x-text="photo.preformatted.lens"></span>
    </p>
</div>