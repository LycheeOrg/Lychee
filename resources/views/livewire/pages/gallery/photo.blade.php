<div class="w-full" x-data="{ detailsOpen: false, editOpen: false }">
    <!-- toolbar -->
    <x-header.bar>
        <x-header.back />
        <x-header.title>{{ $photo->title }}</x-header.title>
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
        @can(App\Policies\PhotoPolicy::CAN_EDIT, [App\Models\Photo::class, $photo])
            <x-header.button x-on:click="editOpen = ! editOpen" icon="pencil" fill=""
                @keydown.window="if (event.keyCode === 69 && $focus.focused() === undefined) { event.preventDefault(); editOpen = ! editOpen }"
                {{-- 69 = e --}} x-bind:class="editOpen ? 'fill-sky-500' : 'fill-neutral-400'" />
        @endcan
        <x-header.button x-on:click="detailsOpen = ! detailsOpen" icon="info" fill=""
            @keydown.window="if (event.keyCode === 73 && $focus.focused() === undefined) { event.preventDefault(); detailsOpen = ! detailsOpen }"
            {{-- 73 = i --}} x-bind:class="detailsOpen ? 'fill-sky-500' : 'fill-neutral-400'" />

    </x-header.bar>
    <div class="w-full flex h-[calc(100%-3.5rem)] overflow-hidden">
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
                {{-- 79 - o --}}
                x-on:click="rotateOverlay()">
                @if ($photo->isVideo())
                    {{-- This is a video file: put html5 player --}}
                    <video width="auto" height="auto" id='image' controls
                        class='absolute top-7 bottom-7 left-7 right-7 m-auto w-auto h-auto max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'
                        autobuffer {{ $flags->can_autoplay ? 'autoplay' : '' }}
                        data-tabindex='{{ Helpers::data_index() }}'>
                        <source src='{{ URL::asset($photo->size_variants->getOriginal()->url) }}' />
                        Your browser does not support the video tag.
                    </video>
                @elseif($photo->isRaw())
                    {{-- This is a raw file: put a place holder --}}
                    <img id='image' alt='big'
                        class='absolute top-7 bottom-7 left-7 right-7 m-auto w-auto h-auto max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'
                        src='{{ URL::asset('img/placeholder.png') }}' draggable='false'
                        data-tabindex='{{ Helpers::data_index() }}' />
                @elseif ($photo->live_photo_short_path === null)
                    {{-- This is a normal image: medium or original --}}
                    @if ($photo->size_variants->getMedium() !== null)
                        <img id='image' alt='medium'
                            class='absolute top-7 bottom-7 left-7 right-7 m-auto w-auto h-auto max-w-[calc(100%-56px)] max-h-[calc(100%-56px)] animate-zoomIn'
                            src='{{ URL::asset($photo->size_variants->getMedium()->url) }}'
                            @if ($photo->size_variants->getMedium2x() !== null) srcset='{{ URL::asset($photo->size_variants->getMedium()->url) }} {{ $photo->size_variants->getMedium()->width }}w,
							{{ URL::asset($photo->size_variants->getMedium2x()->url) }} {{ $photo->size_variants->getMedium2x()->width }}w' @endif
                            data-tabindex='{{ Helpers::data_index() }}' />
                    @else
                        <img id='image' alt='big'
                            class='absolute top-7 bottom-7 left-7 right-7 m-auto w-auto h-auto max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]
						    bg-contain bg-center bg-no-repeat animate-zoomIn'
                            style='background-image: url({{ URL::asset($photo->size_variants->getSmall()?->url) }})'
                            src='{{ URL::asset($photo->size_variants->getOriginal()->url) }}' draggable='false'
                            data-tabindex='{{ Helpers::data_index() }}' />
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
                <x-gallery.photo.next-previous :photo="$this->previousPhoto" :albumId="$albumId" :is_next="false" @keyup.left.window="Alpine.navigate($el.getAttribute('href'));" wire:navigate.hover />
            @endif
            @if ($this->nextPhoto !== null)
                <x-gallery.photo.next-previous :photo="$this->nextPhoto" :albumId="$albumId" :is_next="true" @keyup.right.window="Alpine.navigate($el.getAttribute('href'));" wire:navigate.hover />
            @endif
            @can(App\Policies\PhotoPolicy::CAN_EDIT, [App\Models\Photo::class, $photo])
                <div class="absolute top-0 w-full bg-red-500">
                    <span class="absolute top-0 left-1/2 -translate-x-1/2 bg-gradient-to-b from-black rounded-b-xl p-1">
                        <x-header.button x-on:click="$wire.set_star()" fill=""
                            class="hover:fill-yellow-500 {{ $photo->is_starred ? 'fill-yellow-500' : 'fill-white' }}"
                            icon="star" @keydown.window="if (event.keyCode === 70) { $wire.set_star(); }"
                            {{-- 70 = f --}} />
                        @if ($flags->can_rotate)
                            <x-header.button x-on:click="$wire.rotate_ccw()" fill=""
                                class="fill-white hover:fill-sky-500" icon="counterclockwise"
                                @keydown.window="if (event.keyCode === 37 && event.ctrlKey) { $wire.rotate_ccw(); }"
                                {{-- 37 = left arrow --}} />
                            <x-header.button x-on:click="$wire.rotate_cw()" fill=""
                                class="fill-white hover:fill-sky-500" icon="clockwise"
                                @keydown.window="if (event.keyCode === 39 && event.ctrlKey) { $wire.rotate_cw(); }"
                                {{-- 39 = right arrow --}} />
                        @endif
                        <x-header.button x-on:click="$wire.delete()" fill="" class="fill-red-800 hover:fill-red-600"
                            icon="trash"
                            @keydown.window="if (event.ctrlKey && (event.keyCode === 46 || event.keyCode === 8)) { $wire.delete(); }"
                            {{-- 46 = dell, 8 = backspace --}} />
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
        <aside id="lychee_sidebar_container" class="h-full relative overflow-clip w-0 bg-dark-800 transition-all"
            :class=" detailsOpen ? 'w-[360px]' : 'w-0 translate-x-full'">
            <livewire:modules.photo.sidebar :photo="$this->photo" />
        </aside>
    </div>
</div>
