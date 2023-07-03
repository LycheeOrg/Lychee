<div id="image_overlay" class="absolute bottom-7 left-7 text-white text-shadow">
	<h1 class=" text-3xl">{{ $title }}</h1>
	<p class="mt-1 text-xl">
	@switch($type)
		@case(App\Enum\Livewire\PhotoOverlayMode::DESC)
			{{ $description }}
		@break
		@case(App\Enum\Livewire\PhotoOverlayMode::DATE)
			@if($camera_date)
			<a>
				<span title='Camera Date'><a class='camera-slr'><svg class='iconic w-4 h-4 fill-white m-0 mr-1 -mt-1 inline-block'><use xlink:href='#camera-slr' /></svg></a></span>				{{ $date }}
			</a>
			@else
			{{ $date }}
			@endif
		@break
		@case(App\Enum\Livewire\PhotoOverlayMode::EXIF)
			{{ html_entity_decode($exif1) }}
			<br>
			{{ $exif2 }}
		@break
		@default
	@endswitch
	</p>
</div>