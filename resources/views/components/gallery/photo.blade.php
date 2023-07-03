<div
	wire:click="$emit('openPhoto', '{{ $photo_id }}')"
	style="{{ $style }}"
	class='photo group absolute shadow-md' {{-- ${disabled ? `disabled` : ``}'--}}
	data-album-id='{{ $album_id }}'
	data-id='{{ $photo_id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	>
	<span class="thumbimg w-full h-full border-none object-cover {{ $class }}">
		<img
			alt='Photo thumbnail'
			@class([
				'w-full h-full border-none object-cover',
				'lazyload' => $is_lazyload])
			@if (!$is_lazyload)
				data-tabindex='{{ Helpers::data_index() }}'
			@endif

			{!! $src !!}
			{!! $srcset !!}
			{!! $srcset2x !!}

			data-overlay='false'
			draggable='false'
		/>
	</span>
	<div class='overlay w-full absolute bottom-0 m-0 opacity-0 bg-gradient-to-t from-[#00000099] group-hover:opacity-100 transition-opacity ease-out'>
		<h1 class=" min-h-[19px] mt-3 mb-1 ml-4 text-white text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis"
			title='{{ $title }}'>{{ $title }}</h1>
		@if($taken_at !== "")
			<a class="block mt-0 mr-0 mb-3 ml-4 text-xs text-neutral-500"><span title='Camera Date'>
				<x-icons.iconic icon='camera-slr' class=" w-2 h-2 m-0 mr-1 fill-neutral-500" /></span>{{ $taken_at }}</a>
		@else
			<a class="block mt-0 mr-0 mb-3 ml-4 text-xs text-neutral-500">{{ $created_at }}</a>
		@endif
	</div>
	@auth
		<div class='badges absolute mt-[-1px] ml-1'>
			@if($is_starred)
				<x-gallery.badge class='badge--star bg-yellow-500' icon='star'/>
			@endif
			@if($is_public)
				<x-gallery.badge class='badge--public bg-orange-400' icon='eye'/>
			@endif
		</div>
	@endauth
</div>