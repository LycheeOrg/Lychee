<div id="lychee_sidebar"
    x-data="photoSidebarPanel()"
    x-on:photo-updated.window="refreshSidebar($store.photo)"
    class="border-t border-solid border-primary-500 text-text-main-0 w-[360px] h-full bg-bg-800 pr-4 break-words overflow-x-clip overflow-y-scroll">
    <div class="grid grid-cols-[auto minmax(0, 1fr)] mt-8">
        <h1 class="col-span-2 text-center text-lg font-bold my-4">
            {{ __('lychee.ALBUM_ABOUT') }}
        </h1>
        <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">
            {{ __('lychee.PHOTO_BASICS') }}
        </h2>
        <x-gallery.photo.line if="true"
        :locale="__('lychee.PHOTO_TITLE')" value="photo.title" />
        <x-gallery.photo.line if="true"
        :locale="__('lychee.PHOTO_UPLOADED')" value="preformatted.created_at" />
        <template x-if="preformatted.description">
            <span class="col-span-2 py-0.5 pl-3 text-sm">{{ __('lychee.PHOTO_DESCRIPTION') }}</span>
        </template>
        <template x-if="preformatted.description">
            <div class="pb-0.5 pt-4 pl-8 col-span-2 prose prose-invert prose-sm" x-html="preformatted.description">
            </div>
        </template>
        <template x-if="precomputed.is_video">
            <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">{{ __('lychee.PHOTO_VIDEO') }}</h2>
        </template>
        <template x-if="! precomputed.is_video">
            <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">{{ __('lychee.PHOTO_IMAGE') }}</h2>
        </template>
        <x-gallery.photo.line if="true"
            :locale="__('lychee.PHOTO_SIZE')" value="preformatted.filesize" />
        <x-gallery.photo.line if="photo.type"
            :locale="__('lychee.PHOTO_FORMAT')" value="photo.type" />
        <x-gallery.photo.line if="preformatted.resolution"
            :locale="__('lychee.PHOTO_RESOLUTION')"
            value="preformatted.resolution" />
        <x-gallery.photo.line if="photo.duration && precomputed.is_video"
            :locale="__('lychee.PHOTO_DURATION')" value="photo.duration" />
        <x-gallery.photo.line if="photo.fps && precomputed.is_video"
            :locale="__('lychee.PHOTO_FPS')" value="photo.fps" />
        <template x-if="photo.tags.length > 0">
            <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">
                {{ __('lychee.PHOTO_TAGS') }}
            </h2>
        </template>
        <template x-if="photo.tags.length > 0">
            <p class="py-0.5 pl-3 col-span-2 text-sm">
                <template x-for="tag in photo.tags">
                    <a class="text-xs cursor-pointer rounded-full py-1.5 px-2.5 mr-1.5 mb-2.5 bg-black/50"
                        x-text="tag"></a>
                </template>
            </p>
        </template>
        <template x-if="precomputed.has_exif">
            <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">
                {{ __('lychee.PHOTO_CAMERA') }}
            </h2>
        </template>
        <x-gallery.photo.line if="preformatted.taken_at"
            :locale="__('lychee.PHOTO_CAPTURED')"
            value="preformatted.taken_at" />
        <x-gallery.photo.line if="photo.make"
            :locale="__('lychee.PHOTO_MAKE')"
            value="photo.make" />
        <x-gallery.photo.line if="photo.model"
            :locale="__('lychee.PHOTO_TYPE')"
            value="photo.model" />
        <x-gallery.photo.line if="photo.lens"
            :locale="__('lychee.PHOTO_LENS')" value="photo.lens" />
        <x-gallery.photo.line if="preformatted.shutter"
            :locale="__('lychee.PHOTO_SHUTTER')"
            value="preformatted.shutter" />
        <x-gallery.photo.line if="preformatted.aperture"
            :locale="__('lychee.PHOTO_APERTURE')"
            value="'ƒ / ' + preformatted.aperture" />
        <x-gallery.photo.line if="photo.focal"
            :locale="__('lychee.PHOTO_FOCAL')"
            value="photo.focal" />
        <x-gallery.photo.line if="photo.iso"
            :locale="sprintf(__('lychee.PHOTO_ISO'), '')" value="preformatted.iso" />
        <template x-if="precomputed.has_location">
            <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">
                {{ __('lychee.PHOTO_LOCATION') }}
            </h2>
        </template>
        <div
            data-layer='{{ $this->map_provider->getLayer() }}'
            data-provider='{{ $this->map_provider->getAtributionHtml() }}'
            data-asset='{{ URL::asset('/') }}'
            id="leaflet_map_single_photo" class="col-span-2 h-48 bg-red-500 my-0.5 mx-3"
            x-init="$nextTick(() => displayMap())"
            x-show="precomputed.has_location"></div>
        <x-gallery.photo.line if="precomputed.has_location && preformatted.latitude"
            :locale="__('lychee.PHOTO_LATITUDE')"
            value="preformatted.latitude" />
        <x-gallery.photo.line if="precomputed.has_location && preformatted.longitude"
            :locale="__('lychee.PHOTO_LONGITUDE')"
            value="preformatted.longitude" />
        <x-gallery.photo.line if="precomputed.has_location && preformatted.altitude"
            :locale="__('lychee.PHOTO_ALTITUDE')"
            value="preformatted.altitude" />
        <x-gallery.photo.line if="precomputed.has_location && photo.location"
            :locale="__('lychee.PHOTO_LOCATION')"
            value="photo.location" />
        <x-gallery.photo.line if="precomputed.has_location && photo.location !== null"
            :locale="__('lychee.PHOTO_IMGDIRECTION')"
            value="photo.img_direction" />
        <template x-if="preformatted.license">
            <h2 class="col-span-2 text-text-main-400 font-bold px-3 pt-4 pb-3">
                {{ __('lychee.PHOTO_REUSE') }}
            </h2>
        </template>
        <x-gallery.photo.line if="preformatted.license"
            :locale="__('lychee.PHOTO_LICENSE')"
            value="preformatted.license" />
    </div>
</div>
