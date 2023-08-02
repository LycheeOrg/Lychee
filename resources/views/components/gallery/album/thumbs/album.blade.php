<a href="{{ route('livewire-gallery-album', ['albumId' => $id])}}"
	wire:navigate
	@class([
	"album-thumb block relative aspect-square
	w-[calc(33vw-9px-4px)] ml-1 mt-1
	sm:w-[calc(25vw-9px-10px)] sm:ml-2 sm:mt-2
	md:w-[calc(20vw-9px-18px)] md:ml-4 md:mt-4
	lg:w-[calc(16vw-9px-15px)] lg:ml-5 lg:mt-5
	xl:w-[calc(14vw-9px-22px)] xl:ml-6 xl:mt-6
	2xl:w-52 2xl:ml-7 2xl:mt-7
	animate-zoomIn
	group",
	    'blurred' => $is_nsfw_blurred
	])
	 {{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $is_nsfw ? '1' : '0'}}'>
	<x-gallery.album.thumbs.album-thumb class="group-hover:border-sky-500 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<x-gallery.album.thumbs.album-thumb class="group-hover:border-sky-500 group-hover:rotate-6 group-hover:translate-x-3  group-hover:-translate-y-2"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<x-gallery.album.thumbs.album-thumb class="group-hover:border-sky-500"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<div class='overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[calc(100%-2px)] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm'>
		<h1 class="w-full pt-3 pb-1 pr-1 pl-4 text-sm text-white font-bold text-ellipsis whitespace-nowrap overflow-x-hidden" title='{{ $title }}'>{{ $title }}</h1>
		<span class="block mt-0 mr-0 mb-3 ml-4 text-2xs text-neutral-400">{{ $data['created_at'] ?? '' }}</span>
	</div>

	@auth
	<div class='badges absolute mt-[-1px] ml-1 top-0 left-0'>
		@if ($is_nsfw)
			<x-gallery.badge class='badge--nsfw bg-[#ff82ee]' icon='warning' />
		@endif
		@switch($id)
			@case(App\SmartAlbums\StarredAlbum::ID)
			<x-gallery.badge class='badge--star bg-yellow-500' icon='star' />
			@break
			@case(App\SmartAlbums\PublicAlbum::ID)
			<x-gallery.badge class='badge--public bg-green-600' icon='eye' />
			@break
			@case(App\SmartAlbums\UnsortedAlbum::ID)
			<x-gallery.badge class='badge--unsorted bg-red-700' icon='list' />
			@break
			@case(App\SmartAlbums\RecentAlbum::ID)
			<x-gallery.badge class='badge--recent bg-blue-700' icon='clock' />
			@break
			@case(App\SmartAlbums\OnThisDayAlbum::ID)
			<x-gallery.badge class='badge--onthisday bg-green-600' icon='calendar' />
			@break
			@default
		@endswitch
		@if ($is_public)
			<x-gallery.badge class='badge--ispublic {{ $is_link_required ? "bg-orange-400" : "bg-green-600"}}' icon='eye' />
		@endif
		@if ($is_password_required)
			<x-gallery.badge class='badge--locked bg-orange-400' icon='lock-locked' />
		@endif
		@if ($is_tag_album == "1")
			<x-gallery.badge class='badge--tag bg-green-600' icon='tag' />
		@endif
		@if ($has_cover_id)
			<x-gallery.badge class='badge--cover bg-yellow-500' icon='folder-cover' />
		@endif
	</div>
	@endauth
	@if ($has_subalbum)
	<div class='album_counters absolute right-2 top-1.5 flex flex-row gap-1 justify-end text-right font-bold font-sans drop-shadow-md'>
		<div class="layers relative py-1 px-1">
			<x-icons.iconic icon="layers" class=" fill-white w-3 h-3" />
		</div>
	</div>
	@endif
</a>