<div
	wire:click="$emit('openAlbum', '{{ $id }}')"
	@class([
		'album-thumb relative w-52 h-52 mt-7 ml-7 group',
	    'blurred' => $is_nsfw_blurred
	])
	 {{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $is_nsfw ? '1' : '0'}}'>
	<x-gallery.album-thumb class="group-hover:border-sky-500 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<x-gallery.album-thumb class="group-hover:border-sky-500 group-hover:rotate-6 group-hover:translate-x-3  group-hover:-translate-y-2"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<x-gallery.album-thumb class="group-hover:border-sky-500"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />

<div class='overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[206px] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm'>
	<h1 class="w-48 pt-3 pb-1 pr-1 pl-4 text-sm text-white font-bold text-ellipsis whitespace-nowrap overflow-x-hidden" title='{{ $title }}'>{{ $title }}</h1>
	<a class="block mt-0 mr-0 mb-3 ml-4 text-2xs text-neutral-400">{{ $data['created_at'] ?? '' }}</a>
</div>

@auth
<div class='badges absolute mt-[-1px] ml-1'>
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
<div class='album_counters absolute right-2 top-2 flex flex-row gap-1 justify-end text-right font-bold font-sans drop-shadow-md'>
	<a class="layers relative py-1 px-0">
		<x-icons.iconic icon="layers" class=" fill-white w-3 h-3" />
	</a>
</div>
@endif
</div>