<x-view.container>
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		@php
			Helpers::data_index_set(100);
		@endphp
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
		<div class="{{ $layout }}">
			@foreach ($this->album->photos as $data)
				<x-photo :data="$data" />
			@endforeach
		</div>
	</div>
</x-view.container>