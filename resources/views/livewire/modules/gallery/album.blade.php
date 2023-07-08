<div id="lychee_view_content" 
    x-init="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    $wire.loadAlbum(width - 2*28 -20);"
    {{-- We remove 2x padding of 7rem + 20px for the scroll bar --}}
    x-on:resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    $wire.loadAlbum(width - 2*28);"
    class="relative flex flex-wrap flex-auto flex-shrink-0 w-full justify-start overflow-clip-auto"
    >

@if(!$isOpen)
    @if ($this->header_url !== null)
        <div class="w-full h-1/2 mb-4">
            <img class="absolute block top-0 left-0 w-full h-1/2 object-cover object-center z-0" src="{{ URL::asset($this->header_url) }}">
            <div class="h-full pl-7 pt-7 relative text-shadow-sm w-full bg-gradient-to-b from-black/20 via-80%">
                <h1 class="font-bold text-4xl text-white">{{ $this->album->title }}</h1>
                @if($this->album->min_taken_at !== null)
                <span class="text-neutral-200 text-sm">{{ $this->album->min_taken_at->format("M Y") }}
                    @if($this->album->max_taken_at->format("M Y") !== $this->album->min_taken_at->format("M Y"))
                        - {{ $this->album->max_taken_at->format("M Y") }}
                    @endif
                </span>
                @endif
                <div class="absolute flex flex-col bottom-0 right-0 w-full pl-7 pb-7 bg-gradient-to-t from-black/60 via-80%">
                    <span class="block text-neutral-200 text-sm">{{ __('lychee.ALBUM_CREATED') }} {{ $this->album->created_at->format('M j, Y g:i:s A e') }}</span>
                    @if($this->album->children->count() > 0)
                        <span class="block text-neutral-200 text-sm">{{ $this->album->children->count() }} {{ __('lychee.ALBUM_SUBALBUMS') }}</span>
                    @endif
                    <span class="block text-neutral-200 text-sm">{{ $this->album->photos->count() }} {{ __('lychee.ALBUM_IMAGES') }}</span>
                </div>
            </div>
        </div>
        @if($this->album->description !== null)
        <div>
            {{ $this->album->description }}
        </div>
        @endif
    @endif
@else
<div class="w-full h-1/2 m-7 mb-4 flex">
    <div>
    {{ __('lychee.ALBUM_TITLE') }}
    <x-forms.inputs.text wire:model='title' />
    {{ __('lychee.ALBUM_DESCRIPTION') }}
    <textarea wire:model="description"></textarea>
    {{ __('lychee.ALBUM_ORDERING' )}}

    <x-forms.buttons.action wire:action='save'>{{ __('lychee.SAVE') }}</x-forms.buttons.action>
    </div>
    <div>
    </div>
</div>
@endif
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
            @if ($this->album->photos?->count() > 0)
                <x-gallery.divider>
                    {{ __('lychee.PHOTOS') }}
                </x-gallery.divider>
            @endif
        @endif
    @endisset
        <div 
            @class([
                'squares' => $layout === \App\Enum\Livewire\AlbumMode::SQUARE,
                'm-7 relative w-full' => $layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED, // only one working for now
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
<x-footer />
</div>
