<div class="w-full flex flex-col" x-data="{
    detailsOpen: $wire.entangle('sessionFlags.are_photo_details_open'),
    isFullscreen: $wire.entangle('sessionFlags.is_fullscreen'),
    editOpen: false,
    donwloadOpen: false
}"
    @keydown.window="if (event.keyCode === 70 && $focus.focused() === undefined) { event.preventDefault(); isFullscreen = ! isFullscreen }"
    {{-- 70 = f --}}>
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back />
        <x-header.title>
            @if ($photo->is_starred)
            <x-icons.iconic icon="star" fill='fill-yellow-400' class="my-0 w-3 h-3 mb-1 mr-0 ml-0" />
            @endif
            {{ $photo->title }}
        </x-header.title>
        {{-- <a class="button button--star" id="button_star"><x-icons.iconic icon="star" /></a>
        <a class="button button--eye" id="button_visibility"><x-icons.iconic icon="eye" /></a>
        <a class="button button--rotate" id="button_rotate_ccwise"><x-icons.iconic icon="counterclockwise" /></a>
        <a class="button button--rotate" id="button_rotate_cwise"><x-icons.iconic icon="clockwise" /></a>
        <a class="button button--share" id="button_share"><x-icons.iconic class='ionicons' icon="share-ion" /></a>
        <a wire:click='toggleSideBar' class="button button--info" id="button_info"><x-icons.iconic icon="info" /></a>
        <a class="button button--map" id="button_map"><x-icons.iconic icon="map" /></a>
        <a class="button" id="button_move"><x-icons.iconic icon="folder" /></a>
        <a class="button" id="button_trash"><x-icons.iconic icon="trash" /></a>
        <a class="button" id="button_fs_enter"><x-icons.iconic icon="fullscreen-enter" /></a>
        <a class="button" id="button_fs_exit"><x-icons.iconic icon="fullscreen-exit" /></a>
        <a class="header__divider"></a> --}}
        {{-- <x-header.button wire:click="openContextMenu" icon="ellipses" /> --}}
        @can(App\Policies\PhotoPolicy::CAN_DOWNLOAD, [App\Models\Photo::class, $photo])
            <x-header.button x-on:click="donwloadOpen = ! donwloadOpen" icon="cloud-download" fill=""
                class="fill-neutral-400" x-bind:class="donwloadOpen ? 'fill-sky-500' : 'fill-neutral-400'" />
        @endcan
        @can(App\Policies\PhotoPolicy::CAN_EDIT, [App\Models\Photo::class, $photo])
            <x-header.button x-on:click="editOpen = ! editOpen" icon="pencil" fill=""
                @keydown.window="if (event.keyCode === 69 && $focus.focused() === undefined) { event.preventDefault(); editOpen = ! editOpen }"
                {{-- 69 = e --}} x-bind:class="editOpen ? 'fill-sky-500' : 'fill-neutral-400'" />
        @endcan
        <x-header.button x-on:click="detailsOpen = ! detailsOpen" icon="info" fill=""
            @class([
                'fill-sky-500' => $sessionFlags->are_photo_details_open,
                'fill-neutral-400' => !$sessionFlags->are_photo_details_open,
            ])
            @keydown.window="if (event.keyCode === 73 && $focus.focused() === undefined) { event.preventDefault(); detailsOpen = ! detailsOpen }"
            {{-- 73 = i --}} x-bind:class="detailsOpen ? 'fill-sky-500' : 'fill-neutral-400'" />

    </x-header.bar>
    <div class="w-full flex h-full overflow-hidden bg-black">
        <div class="w-0 flex-auto relative">
            <div id="imageview" class="absolute top-0 left-0 w-full h-full bg-black " x-data="{
                has_description: {{ $photo->description !== null ? 'true' : 'false' }},
                overlayType: '{{ $overlayType }}',
                rotateOverlay() {
                    switch (this.overlayType) {
                        case 'exif':
                            this.overlayType = 'date';
                            break;
                        case 'date':
                            if (this.has_description) { this.overlayType = 'description'; } else { this.overlayType = 'none'; }
                            break;
                        case 'description':
                            this.overlayType = 'none';
                            break;
                        default:
                            this.overlayType = 'exif';
                    }
                }
            }"
                @keydown.window="if (event.keyCode === 79 && $focus.focused() === undefined) { rotateOverlay() }"
                {{-- 79 - o --}} x-on:click="rotateOverlay()">
                @if ($photo->isVideo())
                    {{-- This is a video file: put html5 player --}}
                    <x-gallery.photo.video-panel :flags="$flags"
                        src="{{ URL::asset($photo->size_variants->getOriginal()->url) }}" />
                @elseif($photo->isRaw())
                    {{-- This is a raw file: put a place holder --}}
                    <x-gallery.photo.photo-panel alt='placeholder' src='{{ URL::asset('img/placeholder.png') }}' />
                @elseif ($photo->live_photo_short_path === null)
                    {{-- This is a normal image: medium or original --}}
                    @if ($photo->size_variants->getMedium() !== null)
                        <x-gallery.photo.photo-panel alt='medium'
                            src='{{ URL::asset($photo->size_variants->getMedium()->url) }}'
                            class='top-7 bottom-7 left-7 right-7' :srcset="$photo->size_variants->getMedium2x() === null
                                ? ''
                                : URL::asset($photo->size_variants->getMedium()->url) .
                                    ' ' .
                                    $photo->size_variants->getMedium()->width .
                                    'w, ' .
                                    URL::asset($photo->size_variants->getMedium2x()->url) .
                                    ' ' .
                                    $photo->size_variants->getMedium2x()->width .
                                    'w'" />
                    @else
                        <x-gallery.photo.photo-panel alt='big'
                            class='top-7 bottom-7 left-7 right-7 bg-contain bg-center bg-no-repeat'
                            style='background-image: url({{ URL::asset($photo->size_variants->getSmall()?->url) }})'
                            src='{{ URL::asset($photo->size_variants->getOriginal()->url) }}' />
                    @endif
                @elseif ($photo->size_variants->getMedium() !== null)
                    {{-- This is a livephoto : medium --}}
                    <div id='livephoto' data-live-photo data-proactively-loads-video='true'
                        data-photo-src='{{ URL::asset($photo->size_variants->getMedium()->url) }}'
                        data-video-src='{{ URL::asset($photo->livePhotoUrl) }}'
                        class='absolute top-7 bottom-7 left-7 right-7 m-auto w-auto h-auto max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'
                        style='width: {{ $photo->size_variants->getMedium()->width }}px; height: {{ $photo->size_variants->getMedium()->height }}px'
                        data-tabindex='{{ Helpers::data_index() }}'>
                    </div>
                @else
                    {{-- This is a livephoto : full --}}
                    <div id='livephoto' data-live-photo data-proactively-loads-video='true'
                        data-photo-src='{{ URL::asset($photo->size_variants->getOriginal()->url) }}'
                        data-video-src='{{ URL::asset($photo->livePhotoUrl) }}'
                        class='absolute top-7 bottom-7 left-7 right-7 m-auto w-auto h-auto max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'
                        style='width: {{ $photo->size_variants->getOriginal()->width }}px; height: {{ $photo->size_variants->getOriginal()->height }}px'
                        data-tabindex='{{ Helpers::data_index() }}'>
                    </div>
                @endif
                <x-gallery.photo.overlay :photo="$photo" />
            </div>
            @if ($this->previousPhoto !== null)
                <x-gallery.photo.next-previous :photo="$this->previousPhoto" :albumId="$albumId" :is_next="false"
                    @keyup.left.window="Alpine.navigate($el.getAttribute('href'));" wire:navigate.hover />
            @endif
            @if ($this->nextPhoto !== null)
                <x-gallery.photo.next-previous :photo="$this->nextPhoto" :albumId="$albumId" :is_next="true"
                    @keyup.right.window="Alpine.navigate($el.getAttribute('href'));" wire:navigate.hover />
            @endif
            @can(App\Policies\PhotoPolicy::CAN_EDIT, [App\Models\Photo::class, $photo])
                <div x-cloak
                    class="absolute top-0 h-1/4 w-full left-0 opacity-0 group hover:opacity-100 transition-opacity duration-500 ease-in-out 
                     bg-gradient-to-b from-black/30">
                    <span class="absolute top-7 left-1/2 -translate-x-1/2 p-1 min-w-[25%] text-center">
                        <x-gallery.photo.button
                            icon="star"
                            class="{{ $photo->is_starred ? 'fill-yellow-500 hover:fill-yellow-100' : 'fill-white hover:fill-yellow-500' }}" 
                            x-on:click="$wire.set_star()" @keydown.window="if (event.keyCode === 83) { $wire.set_star(); }"
                            {{-- 83 = s --}}
                            />
                        @if ($flags->can_rotate)
                            <x-gallery.photo.button
                                icon="counterclockwise"
                                class="fill-white hover:fill-sky-500" 
                                @keydown.window="if (event.keyCode === 37 && event.ctrlKey) { $wire.rotate_ccw(); }"
                                {{-- 37 = left arrow --}}
                                />
                            <x-gallery.photo.button
                                icon="clockwise"
                                class="fill-white hover:fill-sky-500" 
                                @keydown.window="if (event.keyCode === 39 && event.ctrlKey) { $wire.rotate_cw(); }"
                                {{-- 39 = right arrow --}}
                                />
                        @endif
                        <x-gallery.photo.button
                            icon="transfer"
                            class="fill-white hover:fill-sky-500"
                            x-on:click="$wire.move()" @keydown.window="if (event.ctrlKey && (event.keyCode === 77)) { $wire.move(); }"
                            {{-- 77 = m --}}
                        />
                        <x-gallery.photo.button
                            icon="trash"
                            class="fill-white hover:fill-red-600" 
                            x-on:click="$wire.delete()" @keydown.window="if (event.ctrlKey && (event.keyCode === 46 || event.keyCode === 8)) { $wire.delete(); }"
                            {{-- 46 = dell, 8 = backspace --}}
                        />
                    </span>
                </div>
            @endcan
        </div>
        @can(App\Policies\PhotoPolicy::CAN_EDIT, [App\Models\Photo::class, $photo])
            <div class="h-full relative overflow-clip w-0 bg-dark-800 transition-all"
                :class=" editOpen ? 'w-full' : 'w-0 translate-x-full'">
                <livewire:modules.photo.properties :photo="$this->photo" />
            </div>
        @endcan
        <aside id="lychee_sidebar_container" @class([
            'h-full relative overflow-clip transition-all',
            'w-[360px]' => $sessionFlags->are_photo_details_open,
            'w-0 translate-x-full' => !$sessionFlags->are_photo_details_open,
        ])
            :class=" detailsOpen ? 'w-[360px]' : 'w-0 translate-x-full'">
            <livewire:modules.photo.sidebar :photo="$this->photo" />
        </aside>
    </div>
    @can(App\Policies\PhotoPolicy::CAN_DOWNLOAD, [App\Models\Photo::class, $photo])
        <x-gallery.photo.download :photo="$this->photo" />
    @endcan
</div>
