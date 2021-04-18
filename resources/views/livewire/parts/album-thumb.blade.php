@if (isset($data['thumb']))
	@php
	// dd($data['thumb']);
	$isVideo = $data['thumb']['type'] && Str::contains($data['thumb']['type'],"video");

	@endphp
	@if ($data['thumb']['thumb'] == "uploads/thumb/" && $isVideo)
		<span class="thumbimg">
			<img src='{{ URL::asset('img/play-icon.png') }}' alt='Photo thumbnail' data-overlay='false' draggable='false'>
		</span>
	@elseif($data['thumb']['thumb'] === "uploads/thumb/" && $data['thumb']['type'] && Str::contains($data['thumb']['type'],"raw"))
		<span class="thumbimg">
			<img src='{{ URL::asset('img/placeholder.png') }}' alt='Photo thumbnail' data-overlay='false' draggable='false'>
		</span>
	@else

	<span class="thumbimg {{ $isVideo ? "video" : ""}}">
		<img class='lazyload' src='{{ URL::asset('img/placeholder.png') }}'
			data-src='{{ URL::asset($data['thumb']['thumb']) }}'
		@if ($data['thumb']['thumb2x'] != "")
			data-srcset='{{ URL::asset($data['thumb']['thumb2x']) }} 2x'
		@endif
			alt='Photo thumbnail' data-overlay='false' draggable='false'>
	</span>
	@endif
@else
	<span class="thumbimg">
		<img src='{{ URL::asset('img/placeholder.png') }}' alt='Photo thumbnail' data-overlay='false' draggable='false'>
	</span>
@endif