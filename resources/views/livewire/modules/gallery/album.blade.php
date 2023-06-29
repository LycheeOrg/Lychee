<x-view.container>
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn" wire:init="loadAlbum">
		@php
			Helpers::data_index_set(100);
		@endphp
		@if($ready_to_load)
		@isset($this->album->children)
			@if($this->album->children->count() > 0)
				@if($this->album->photos?->count() > 0)
				<div class='divider'>
					<h1>{{ __('lychee.ALBUMS') }}</h1>
				</div>
				@endif
				@foreach ($this->album->children as $data)
					<x-album :data="$data" />
				@endforeach
				@if($this->album->photos->count() > 0)
					<div class='divider'>
						<h1>{{ __('lychee.PHOTOS') }}</h1>
					</div>
				@endif
			@endif
		@endisset
		<div @class([
        	'squares' => $layout === \App\Enum\Livewire\AlbumMode::SQUARE,
        	'justified-layout' => $layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED,
        	'masondry' => $layout === \App\Enum\Livewire\AlbumMode::MASONRY,
        	'grid' => $layout === \App\Enum\Livewire\AlbumMode::GRID,
		])="{{ $layout }}"
			 @if($layout === \App\Enum\Livewire\AlbumMode::JUSTIFIED)
			style="height:{{$this->geometry->containerHeight}}px;"
			@endif>
			@for($i = 0; $i < $this->album->photos->count(); $i++)
				<x-photo :data="$this->album->photos[$i]" :geometry="$this->geometry->boxes->get($i)" />
			@endfor
		</div>
		@else
			Loading...
		@endif
	</div>
</x-view.container>