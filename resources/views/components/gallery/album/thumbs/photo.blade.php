<a
	href="{{ route('livewire-gallery-photo',['albumId'=>$album_id, 'photoId' => $photo_id]) }}"
	style="{{ $style }}"
	class='photo group absolute  shadow-md shadow-black/25 animate-zoomIn' {{-- ${disabled ? `disabled` : ``}'--}}
	data-album-id='{{ $album_id }}'
	data-id='{{ $photo_id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	wire:navigate
	>
	<span class="thumbimg w-full h-full border-none object-cover {{ $class }}">
		<img
			alt='Photo thumbnail'
			@class([
				'w-full h-full border-none object-cover',
				'lazyload' => $is_lazyload])
			{!! $src !!}
			{!! $srcset !!}
			{!! $srcset2x !!}
			data-overlay='false'
			draggable='false'
		/>
	</span>
	<div class='overlay w-full absolute bottom-0 m-0 opacity-0 bg-gradient-to-t from-[#00000066] group-hover:opacity-100 transition-opacity ease-out
	text-shadow-sm'>
		<h1 class=" min-h-[19px] mt-3 mb-1 ml-3 text-white text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">{{ $title }}</h1>
		<span class="block mt-0 mr-0 mb-2 ml-3 text-2xs text-neutral-300">
			@if($taken_at !== "")
				<span title='Camera Date'><x-icons.iconic icon='camera-slr' class="w-2 h-2 m-0 mr-1 fill-neutral-300" /></span>{{ $taken_at }}
			@else
				{{ $created_at }}
			@endif
		</span>
	</div>
	@if($is_video)
	<div class="w-full top-0 h-full absolute hover:opacity-70 transition-opacity duration-300">
		<img class="absolute top-1/2 -translate-y-1/2 aspect-square w-fit h-fit" alt="play"  src="{{ URL::asset("img/play-icon.png") }}" />
	</div>
	@endif
	@auth
		<div class='badges absolute mt-[-1px] ml-1 top-0 left-0'>
			@if($is_starred)
				<x-gallery.badge class='badge--star bg-yellow-500' icon='star'/>
			@endif
			@if($is_public)
				<x-gallery.badge class='badge--public bg-orange-400' icon='eye'/>
			@endif
		</div>
	@endauth
</a>