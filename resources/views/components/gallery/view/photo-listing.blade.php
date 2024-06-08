@if ($this->num_photos > 0)
    <div class="relative w-full h-0 -translate-y-5 text-right pr-7">
        <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="photoLayout.type = 'square'" title="{{ __('lychee.LAYOUT_SQUARES') }}">
            <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-white"
                fill="" icon="squares" x-bind:class="photoLayout.type === 'square' ? 'stroke-primary-400' : 'stroke-neutral-400'" />
        </a>
        <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="photoLayout.type = 'justified'" title="{{ __('lychee.LAYOUT_JUSTIFIED') }}">
            <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:fill-white"
                fill="" icon="justified" x-bind:class="photoLayout.type === 'justified' ? 'fill-primary-400' : 'fill-neutral-400'" />
        </a>
        <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="photoLayout.type = 'masonry'" title="{{ __('lychee.LAYOUT_MASONRY') }}">
            <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-white"
                fill="" icon="masonry" x-bind:class="photoLayout.type === 'masonry' ? 'stroke-primary-400' : 'stroke-neutral-400'" />
        </a>
        <a class="flex-shrink-0 px-1 cursor-pointer group" x-on:click="photoLayout.type = 'grid'" title="{{ __('lychee.LAYOUT_GRID') }}">
            <x-icons.iconic class="my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-white"
                fill="" icon="grid" x-bind:class="photoLayout.type === 'grid' ? 'stroke-primary-400' : 'stroke-neutral-400'" />
        </a>
    </div>
@endif
<div id="photoListing" class="relative w-full my-7 mx-1 sm:m-7" x-data="photoListingPanel(@js($this->photosResource), select)" >
    @foreach ($this->photos as $idx => $photo)
        <x-gallery.album.thumbs.photo :data="$photo" albumId="{{ $this->albumId }}" :idx="$idx" :coverId="$this->flags->cover_id" />
    @endforeach
</div>
