<span class="thumbimg {{ $isVideo ? "video" : ""}}">
	<img class='lazyload'
	src='{{ URL::asset('img/placeholder.png') }}'
	data-src='{{ URL::asset($thumb) }}'
	@if ($thumb2x != "")
		data-srcset='{{ URL::asset($thumb2x) }} 2x'
	@endif
	alt='Photo thumbnail'
	data-overlay='false'
	draggable='false'>
</span>
