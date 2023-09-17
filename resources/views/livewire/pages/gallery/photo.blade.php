<div class="w-full flex flex-col" x-data="photoView(
    $wire.entangle('sessionFlags.are_photo_details_open'),
    @entangle('sessionFlags.is_fullscreen'),
    {{ $photo->description !== null ? 'true' : 'false' }},
    '{{ $overlayType }}')"
    @keydown.window="handleKeydown(event, $wire)">
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back @keydown.escape.window="$wire.back();" />
        <x-header.title>
            @if ($photo->is_starred)
                <x-icons.iconic icon="star" fill='fill-yellow-400' class="my-0 w-3 h-3 mb-1 mr-0 ml-0" />
            @endif
            {{ $photo->title }}
        </x-header.title>
        {{-- 
        <a class="button button--share" id="button_share"><x-icons.iconic class='ionicons' icon="share-ion" /></a>
        <a class="button button--map" id="button_map"><x-icons.iconic icon="map" /></a>
        {{-- <x-header.button wire:click="openContextMenu" icon="ellipses" /> --}}
        @can(App\Policies\PhotoPolicy::CAN_DOWNLOAD, [App\Models\Photo::class, $photo])
            <x-header.button x-on:click="donwloadOpen = ! donwloadOpen" icon="cloud-download" fill=""
                class="fill-neutral-400" x-bind:class="donwloadOpen ? 'fill-sky-500' : 'fill-neutral-400'" />
        @endcan
        @can(App\Policies\PhotoPolicy::CAN_EDIT, [App\Models\Photo::class, $photo])
            <x-header.button x-on:click="editOpen = ! editOpen" icon="pencil" fill=""
                x-bind:class="editOpen ? 'fill-sky-500' : 'fill-neutral-400'" />
        @endcan
        <x-header.button x-on:click="detailsOpen = ! detailsOpen" icon="info" fill=""
            @class([
                'fill-sky-500' => $sessionFlags->are_photo_details_open,
                'fill-neutral-400' => !$sessionFlags->are_photo_details_open,
            ]) x-bind:class="detailsOpen ? 'fill-sky-500' : 'fill-neutral-400'" />

    </x-header.bar>
    <div class="w-full flex h-full overflow-hidden bg-black">
        <div class="w-0 flex-auto relative">
            <div id="imageview" class="absolute top-0 left-0 w-full h-full bg-black flex items-center justify-center"
                x-on:click="rotateOverlay()">
                @if ($photo->isVideo())
                    {{-- This is a video file: put html5 player --}}
                    <x-gallery.photo.video-panel :flags="$flags"
                        x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
                        src="{{ URL::asset($photo->size_variants->getOriginal()->url) }}" />
                @elseif($photo->isRaw())
                    {{-- This is a raw file: put a place holder --}}
                    <x-gallery.photo.photo-panel alt='placeholder' src='{{ URL::asset('img/placeholder.png') }}'
                        x-bind:class="" />
                @elseif ($photo->live_photo_short_path === null)
                    {{-- This is a normal image: medium or original --}}
                    @if ($photo->size_variants->getMedium() !== null)
                        <x-gallery.photo.photo-panel alt='medium'
                            src='{{ URL::asset($photo->size_variants->getMedium()->url) }}'
                            x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
                            :srcset="$photo->size_variants->getMedium2x() === null
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
                            x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
                            style='background-image: url({{ URL::asset($photo->size_variants->getSmall()?->url) }})'
                            src='{{ URL::asset($photo->size_variants->getOriginal()->url) }}' />
                    @endif
                @elseif ($photo->size_variants->getMedium() !== null)
                    {{-- This is a livephoto : medium --}}
                    <div id='livephoto' data-live-photo data-proactively-loads-video='true'
                        data-photo-src='{{ URL::asset($photo->size_variants->getMedium()->url) }}'
                        data-video-src='{{ URL::asset($photo->livePhotoUrl) }}' class='absolute m-auto w-auto h-auto'
                        x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
                        style='width: {{ $photo->size_variants->getMedium()->width }}px; height: {{ $photo->size_variants->getMedium()->height }}px'>
                    </div>
                @else
                    {{-- This is a livephoto : full --}}
                    <div id='livephoto' data-live-photo data-proactively-loads-video='true'
                        data-photo-src='{{ URL::asset($photo->size_variants->getOriginal()->url) }}'
                        data-video-src='{{ URL::asset($photo->livePhotoUrl) }}' class='absolute m-auto w-auto h-auto'
                        x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
                        style='width: {{ $photo->size_variants->getOriginal()->width }}px; height: {{ $photo->size_variants->getOriginal()->height }}px'>
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
                        <x-gallery.photo.button icon="star"
                            class="{{ $photo->is_starred ? 'fill-yellow-500 hover:fill-yellow-100' : 'fill-white hover:fill-yellow-500' }}"
                            x-on:click="$wire.set_star()" />
                        @if ($flags->can_rotate)
                            <x-gallery.photo.button icon="counterclockwise" class="fill-white hover:fill-sky-500"
                                x-on:click="$wire.ccw()" />
                            <x-gallery.photo.button icon="clockwise" class="fill-white hover:fill-sky-500"
                                x-on:click="$wire.cw()" />
                        @endif
                        <x-gallery.photo.button icon="transfer" class="fill-white hover:fill-sky-500"
                            x-on:click="$wire.move()" />
                        <x-gallery.photo.button icon="trash" class="fill-white hover:fill-red-600"
                            x-on:click="$wire.delete()" />
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
        <div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
    bg-black/80 z-50 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
            data-closable="true" x-cloak x-show="donwloadOpen">
            <div class="basicModal transition-opacity ease-in duration-1000
        opacity-100 bg-gradient-to-b from-dark-300 to-dark-400
        relative w-[500px] text-sm rounded-md text-neutral-400 animate-moveUp"
                role="dialog" x-on:click.away="donwloadOpen = !donwloadOpen">
                <x-gallery.photo.download :photo="$this->photo" />
                <div class="basicModal__buttons">
                    <x-forms.buttons.cancel x-on:click="donwloadOpen = false"
                        class="border-t border-t-black/20 w-full hover:bg-white/[.02]">
                        {{ __('lychee.CLOSE') }}</x-forms.buttons.cancel>
                </div>
            </div>
        </div>
    @endcan
</div>
