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
		<x-photo.play />

	@elseif ($data['thumbUrl'] === "uploads/thumb/" && $isRaw)
		<x-photo.placeholder />

	@elseif (App\Models\Configs::get_value('layout', '0') == "0")
		<x-photo.thumbimg
			class="{{ $class_vid_live }}"
			thumb="{{ $data['thumbUrl'] }}"
			thumb2x="{{ $data['thumb2x'] }}"
			type="square"
		/>
	@else
		@if ($data['small'] !== "")
		<x-photo.thumbimg
			class="{{ $class_vid_live }}"
			thumb="{{ $data['small'] }}"
			thumb2x="{{ $data['small2x'] }}"
			dim="{{ intval($data['small_dim']) }}"
			dim2x="{{ intval($data['small2x_dim']) }}"
		/>

		@elseif ($data['medium'] !== "")
			<x-photo.thumbimg
				class="{{ $class_vid_live }}"
				thumb="{{ $data['medium'] }}"
				thumb2x="{{ $data['medium2x'] }}"
				dim="{{ intval($data['medium_dim']) }}"
				dim2x="{{ intval($data['medium2x_dim']) }}"
			/>

		@elseif (!$isVideo)
			{{-- Fallback for images with no small or medium. --}}
			<x-photo.thumbimg
				class="{{ $isLivePhoto ? " livephoto" : "" }}"
				thumb="{{ $data['url'] }}"
			/>

		@else
			{{-- Fallback for videos with no small (the case of no thumb is handled at the top of this function). --}}
			<x-photo.thumbimg
				class="video"
				thumb="{{ $data['thumbUrl'] }}"
				thumb2x="{{ $data['thumb2x'] }}"
				dim="200"
				dim2x="400"
				/>
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
			<x-icon class='badge--star icn-star' icon='star' />
			@endif
			@if($data['public'] == '1' // && album.json.public !== "1"
			)
			<x-icon class='badge--visible badge--hidden icn-share' icon='eye' />
			@endif
		</div>
	@endif
</div>