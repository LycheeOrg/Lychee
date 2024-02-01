<div class="w-full" x-data="albumView(
    @js(route('livewire-search')),
    @entangle('sessionFlags.nsfwAlbumsVisible'),
    @entangle('sessionFlags.is_fullscreen'),
    @entangle('sessionFlags.are_photo_details_open'),
    @js($rights),
    @js(null),
    @js($this->albumIDs),
    @js($this->photosResource),
    @js(null),
    @js($this->overlayType),
    @js($this->layouts),
    @js(true)
)" @keydown.window="handleKeydown(event)" @popstate.window="handlePopState(event)">
    <!-- toolbar -->
    <x-header.bar class="opacity-0" x-bind:class="isFullscreen ? 'opacity-0 h-0' : 'opacity-100 h-14'">
        <x-header.back wire:navigate href="{{ $this->back }}" />
        <x-header.title>{{ $this->title }}</x-header.title>
        <x-header.actions-menus />
    </x-header.bar>
    <div id="lychee_view_content"
        class="relative flex flex-wrap content-start w-full justify-start overflow-x-clip overflow-y-auto"
        x-bind:class="isFullscreen ? 'h-[calc(100vh-3px)]' : 'h-[calc(100vh-56px)]'">
        <div x-data="searchPanel(
            @entangle('searchQuery'),
            @js($search_minimum_length_required),
            select,
            )"
            class="flex items-center justify-center w-full p-4 flex-wrap mb-5 bg-none transition-all"
            x-bind:class="searchQuery.length < {{ $search_minimum_length_required }} ? 'h-4/5' : ''"
            >
            <div class="w-full flex items-center flex-wrap justify-center" x-cloak>
                <div class="items-center relative text-right">
                    <input
                        class="w-80 px-3 pt-1.5 pb-1 rounded-full placeholder:text-text-main-200 bg-bg-800 border border-solid border-bg-900
                        opacity-60 drop-shadow text-text-main-100 focus:border-primary-500 focus:opacity-100 peer transition-all"
                        x-ref="search" x-model.debounce.500ms="searchQuery" type="text" name="search"
                        placeholder="{{ __('lychee.SEARCH') }}" />
                    <a class="header__clear absolute top-1/2 -translate-y-1/2 right-2 opacity-0 text-text-main-100
                            peer-focus:opacity-100 cursor-pointer text-lg"
                        x-on:click="$refs.search.value = ''">&times;</a>
                </div>
                <div class="items-center text-danger-700 w-full text-center"
                    x-bind:class="hideMessage ? 'opacity-100' : 'opacity-0'">
                    Minimum {{ $search_minimum_length_required }} characters required.
                </div>
            </div>
        </div>
        @if ($this->rights->can_edit && $this->flags->is_base_album)
            <x-gallery.album.menu.menu :album="$this->album" :userCount="$this->num_users" />
        @endif
        @if ($this->albumFormatted !== null && ($this->num_albums > 0 || $this->num_photos > 0))
            <x-gallery.album.hero x-show="! albumFlags.isDetailsOpen" />
            <x-gallery.album.details x-show="! albumFlags.isDetailsOpen" />
        @endif
        @if ($this->num_albums === 0 && $this->num_photos === 0)
            <span class="mt-[33%] w-full text-center text-xl text-text-main-400 align-middle">
                {{ $this->noImagesAlbumsMessage }}
            </span>
        @endif
        @if ($this->num_albums > 0 && $this->num_photos > 0)
            <x-gallery.divider title="{{ __('lychee.ALBUMS') }}" />
        @endif
        @if ($this->num_albums > 0)
            @foreach ($this->albums as $data)
                <x-gallery.album.thumbs.album :data="$data" :strAspectRatioClass="$flags->album_thumb_css_aspect_ratio" />
            @endforeach
        @endif
        @if ($this->num_albums > 0 && $this->num_photos > 0)
            <x-gallery.divider title="{{ __('lychee.PHOTOS') }}" />
        @endif
        @if ($this->num_photos > 0)
            <div class="items-center w-full">
                {{ $this->photos->onEachSide(1)->links() }}
            </div>
        @endif
        <x-gallery.view.photo-listing />
        @if ($this->num_photos > 0)
            <div class="items-center w-full">
                {{ $this->photos->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
    <x-gallery.view.photo />
</div>
