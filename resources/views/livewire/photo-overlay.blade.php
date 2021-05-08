<div id="image_overlay">
	<h1>{{ $title }}</h1>
	<p>
	@switch($type)
		@case('desc')
			{{ $description }}
		@break
		@case('date')
			@if($camera_date)
			<a>
				<span title='Camera Date'><a class='badge camera-slr'><svg class='iconic'><use xlink:href='#camera-slr' /></svg></a></span>
				{{ $date }}
			</a>
			@else
			{{ $date }}
			@endif
		@break
		@case('exif')
			{{ html_entity_decode($exif1) }}
			<br>
			{{ $exif2 }}
		@break
		@default
	@endswitch
	</p>
</div>