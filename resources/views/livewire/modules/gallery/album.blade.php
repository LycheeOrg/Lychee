<div id="lychee_view_content" 
    x-init="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    $wire.loadAlbum(width - 2*28 -20);"
    {{-- We remove 2x padding of 7rem + 20px for the scroll bar --}}
    x-on:resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    $wire.loadAlbum(width - 2*28);"
    {{-- x-data="{ width:0 }" --}}
    class="relative flex flex-wrap flex-auto flex-shrink-0 w-full justify-start"
    >
@php
    Helpers::data_index_set(100);
@endphp
@if ($ready_to_load)
    @isset($this->album->children)
        @if ($this->album->children->count() > 0)
            @if ($this->album->photos?->count() > 0)
                <x-gallery.divider>
                    {{ __('lychee.ALBUMS') }}
                </x-gallery.divider>
            @endif
            @foreach ($this->album->children as $data)
                <x-gallery.album :data="$data" />
            @endforeach
            @if ($this->album->photos->count() > 0)
                <x-gallery.divider>
                    {{ __('lychee.PHOTOS') }}
                </x-gallery.divider>
            @endif
        @endif
        @endisset
        <div 
            @class([
                'squares' => $layout === \App\Enum\Livewire\AlbumMode::SQUARE,
                'm-7 relative w-full' => $layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED,
                'masondry' => $layout === \App\Enum\Livewire\AlbumMode::MASONRY,
                'grid' => $layout === \App\Enum\Livewire\AlbumMode::GRID,
            ])
            @if ($layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED)
                style="height:{{ $this->geometry->containerHeight }}px;"
            @endif
        >
        @for ($i = 0; $i < $this->album->photos->count(); $i++)
            <x-gallery.photo :data="$this->album->photos[$i]" :geometry="$this->geometry->boxes->get($i)" />
        @endfor
    </div>
@else
    <span class="mt-[33%] w-full text-center text-xl text-neutral-400 align-middle">
    Loading...
    </span>
@endif
</div>
