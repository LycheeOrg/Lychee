@if (isset($data['types'][$i]))
	@php
	$isVideo = $data['types'][$i] && Str::contains($data['types'][$i],"video");
	@endphp
	@if ($data['thumbs'][$i] == "uploads/thumb/" && $isVideo)
		<span class="thumbimg">
			<img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'>
		</span>
	@elseif($data['thumbs'][$i] === "uploads/thumb/" && $data['types'][$i] && Str::contains($data['types'][$i],"raw"))
		<span class="thumbimg">
			<img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'>
		</span>
	@else

	<span class="thumbimg {{ $isVideo ? "video" : ""}}">
		<img class='lazyload' src='img/placeholder.png'
			data-src='{{ $data['thumbs'][$i] }}'
		@if ($data['thumbs2x'][$i] != "")
			data-srcset='{{ $data['thumbs2x'][$i] }} 2x'
		@endif
			alt='Photo thumbnail' data-overlay='false' draggable='false'>
	</span>
	@endif
@else
	<span class="thumbimg">
		<img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'>
	</span>
@endif