<a 
{{-- wire:navigate href="{{ route('livewire-gallery-photo',['albumId'=>$album_id, 'photoId' => $photo_id]) }}" --}}
	class='photo group shadow-md shadow-black/25 animate-zoomIn transition-all ease-in duration-200 block absolute'
	data-album-id='{{ $album_id }}'
	data-width='{{ $_w }}'
	data-height='{{ $_h }}'
	data-id='{{ $photo_id }}'
	x-on:contextmenu.prevent='handleContextPhoto($event, $wire)'
	x-on:click='handleClickPhoto($event, $wire)'
	x-bind:class="select.selectedPhotos.includes('{{ $photo_id }}') ? 'outline outline-1 outline-primary-500' : ''"
	>
	<span class="thumbimg w-full h-full border-none {{ $is_video ? 'video' : '' }} {{ $is_livephoto ? 'livephoto' : '' }}">
		<img
			alt='Photo thumbnail'
			class="h-full w-full border-none object-cover object-center {{ $is_lazyload ? 'lazyload' : ''}}"
			{!! $src !!}
			{!! $srcset !!}
			{!! $srcset2x !!}
			data-overlay='false'
			draggable='false'
		/>
	</span>
	<div class='overlay w-full absolute bottom-0 m-0 bg-gradient-to-t from-[#00000066]
	{{ $css_overlay }} text-shadow-sm'>
		<h1 class=" min-h-[19px] mt-3 mb-1 ml-3 text-text-main-0 text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">{{ $title }}</h1>
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
			<x-gallery.badge x-show='$store.photos[{{ $idx }}].is_starred' class='badge--star bg-yellow-500' icon='star'/>
			@if ($is_cover_id)
				<x-gallery.badge class='badge--cover bg-yellow-500' icon='folder-cover' />
			@endif
		</div>
	@endauth
</a>