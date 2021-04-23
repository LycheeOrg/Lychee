	@php
		$isVideo = Str::contains($data['type'], "video");
		$isRaw = Str::contains($data['type'], "raw");
		$isLivePhoto = $data['livePhotoUrl'] != "" && $data['livePhotoUrl'] != null;
		$class_vid_live = ($isVideo ? ' video' : '') . ($isLivePhoto ? ' livephoto' : '');
	@endphp
	<div class='photo ${disabled ? `disabled` : ``}'
		data-album-id='{{ URL::asset($data['album']) }}'
		data-id='{{ $data['id'] }}'
		{{-- data-tabindex='${tabindex.get_next_tab_index()}' --}}
		>

	@if ($data['thumbUrl'] == "uploads/thumb/" && $isLivePhoto) 
		<span class="thumbimg">
			<img src='{{ URL::asset('img/live-photo-icon.png') }}'
				alt='Photo thumbnail'
				data-overlay='false'
				draggable='false'
				{{-- data-tabindex='${tabindex.get_next_tab_index()}' --}}
				></span>

	@elseif ($data['thumbUrl'] == "uploads/thumb/" && $isVideo)
		<span class="thumbimg">
			<img src='{{ URL::asset('img/play-icon.png') }}'
				alt='Photo
				thumbnail'
				data-overlay='false'
				draggable='false'
				{{-- data-tabindex='${tabindex.get_next_tab_index()}' --}}
				></span>

	@elseif ($data['thumbUrl'] === "uploads/thumb/" && $isRaw)
		<span class="thumbimg">
			<img src='{{ URL::asset('img/placeholder.png') }}'
				alt='Photo
				thumbnail'
				data-overlay='false'
				draggable='false'
				{{-- data-tabindex='${tabindex.get_next_tab_index()}' --}}
				></span>

	@elseif (false && $lychee['layout'] == "0")
		<span class="thumbimg {{ $class_vid_live }}">
			<img class='lazyload' src='{{ URL::asset('img/placeholder.png') }}'
				data-src='{{ URL::asset($data['thumbUrl']) }}'
				@if (isset($data["thumb2x"]) && $data["thumb2x"] !== "")
					data-srcset='{{ URL::asset($data["thumb2x"]) }} 2x'
				@endif
				alt='Photo thumbnail'
				data-overlay='false'
				draggable='false' >
		</span>
	@else
		@if ($data['small'] !== "")
		<span class="thumbimg {{ $class_vid_live }}">
			<img class='lazyload' src='{{ URL::asset('img/placeholder.png') }}'
				data-src='{{ URL::asset($data['small']) }}'
				@if (isset($data["small2x"]) && $data['small2x'] !== "")
					data-srcset='{{ URL::asset($data['small']) }} {{ intval($data['small_dim']) }}w, {{ URL::asset($data['small2x']) }} {{ intval($data['small2x_dim']) }}w'
				@endif
				alt='Photo thumbnail'
				data-overlay='false'
				draggable='false' >
			</span>

		@elseif ($data['medium'] !== "")
			<span class="thumbimg {{ $class_vid_live }}">
				<img class='lazyload' src='{{ URL::asset('img/placeholder.png') }}'
					data-src='{{ URL::asset($data['medium']) }}'
					@if (isset($data["medium2x"]) && $data['medium2x'] !== "")
						data-srcset='{{ URL::asset($data['medium']) }} {{ intval($data['medium_dim']) }}w, {{ URL::asset($data['medium2x']) }} {{ intval($data['medium2x_dim']) }}w'
					@endif
					alt='Photo thumbnail'
					data-overlay='false'
					draggable='false' >
			</span>

		@elseif (!$isVideo)
			{{-- Fallback for images with no small or medium. --}}
			<span class="thumbimg {{ $isLivePhoto ? " livephoto" : "" }}">
			<img class='lazyload' src='{{ URL::asset('img/placeholder.png') }}'
				data-src='{{ URL::asset($data['url']) }}'
				alt='Photo thumbnail'
				data-overlay='false'
				draggable='false' >
			</span>

		@else
			{{-- Fallback for videos with no small (the case of no thumb is handled at the top of this function). --}}
			<span class="thumbimg video">
				<img class='lazyload' src='{{ URL::asset('img/placeholder.png') }}'
					data-src='{{ $data['thumbUrl'] }}'
					@if (isset($data["thumb2x"]) && $data["thumb2x"] !== "")
						data-srcset='{{ URL::asset($data['thumbUrl']) }} 200w, {{ URL::asset($data["thumb2x"]) }} 400w'
					@endif
					alt='Photo thumbnail' data-overlay='false' draggable='false' >
			</span>
		@endif
	@endif

	<div class='overlay'>
		<h1 title='$${data.title}'>{{ $data['title'] }}</h1>

	@if ($data['takedate'] !== "") 
		<a><span title='Camera Date'><svg class='iconic'><use xlink:href='#camera-slr' /></svg></span>{{ $data['takedate'] }}</a>
	@else
		<a>{{ $data['sysdate'] }}</a>
	@endif
	</div>

	@if (AccessControl::is_logged_in())
		<div class='badges'>
			@if($data['star'] == '1')
			<a class='badge badge--star icn-star'><svg class='iconic'><use xlink:href='#star' /></svg></a>
			@endif
			@if($data['public'] == '1' // && album.json.public !== "1"
			)
			<a class='badge badge--visible badge--hidden icn-share'><svg class='iconic'><use xlink:href='#eye' /></svg></a>
			@endif
		</div>
	@endif
</div>