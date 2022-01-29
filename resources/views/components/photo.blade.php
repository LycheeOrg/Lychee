<div wire:click="$emit('openPhoto', '{{ $photo_id }}')"
	style="--w: {{ $_w }};--h: {{ $_h }}"
	class='photo' {{-- ${disabled ? `disabled` : ``}'--}}
	data-album-id='{{ $album_id }}'
	data-id='{{ $photo_id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	>
	@if ($show_live)
		@include('components.photo.thumb-live')

	@elseif ($show_play)
		@include('components.photo.thumb-play')

	@elseif ($show_placeholder)
		@include('components.photo.thumb-placeholder')

	@else
		<span class="thumbimg {{ $class }}">
			<img class='lazyload'
			{!! $src !!}
			{!! $srcset !!}
			{!! $srcset2x !!}
			alt='Photo thumbnail'
			data-overlay='false'
			draggable='false' >
		</span>
	@endif

	<div class='overlay'>
		<h1 title='{{ $title }}'>{{ $title }}</h1>

	@if($taken_at !== "")
		<a><span title='Camera Date'><x-iconic icon='camera-slr' /></span>{{ $taken_at }}</a>
	@else
		<a>{{ $created_at }}</a>
	@endif
	</div>

	{{-- @if (AccessControl::is_logged_in())
		<div class='badges'>
			@if($is_starred)
			<x-icon class='badge--star icn-star' icon='star' />
			@endif
			@if($is_public)
			<x-icon class='badge--visible badge--hidden icn-share' icon='eye' />
			@endif
		</div>
	@endif --}}
</div>