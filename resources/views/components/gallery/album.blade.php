<div
	wire:click="$emit('openAlbum', '{{ $id }}')"
	class="relative w-52 h-52"
	 {{-- @class([
		''
    'album',
    'blurred' => $is_nsfw_blurred
     ]) --}}
	 {{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $is_nsfw ? '1' : '0'}}'>
	@for ($i = 0; $i < 3; $i++)
		<x-gallery.album-thumb type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	@endfor

<div class='overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[206px] bottom-0 bg-gradient-to-t from-[#00000099]'>
	<h1 class="w-48 pt-3 pb-1 pr-1 pl-4 text-sm text-white font-bold text-ellipsis whitespace-nowrap overflow-x-hidden" title='{{ $title }}'>{{ $title }}</h1>
	<a class="block mt-0 mr-0 mb-3 ml-4 text-xs text-gray-400">{{ $data['created_at'] ?? '' }}</a>
</div>

@if (Auth::check())
<div class='badges'>
	@if ($is_nsfw)
		<x-icons.badge class='badge--nsfw icn-warning' icon='warning' />
	@endif
	@switch($id)
		@case(App\SmartAlbums\StarredAlbum::ID)
		<x-icons.badge class='badge--star icn-star' icon='star' />
		@break
		@case(App\SmartAlbums\PublicAlbum::ID)
		<x-icons.badge class='badge--visible badge--not--hidden icn-share' icon='eye' />
		@break
		@case(App\SmartAlbums\UnsortedAlbum::ID)
		<x-icons.badge class='badge--visible' icon='list' />
		@break
		@case(App\SmartAlbums\RecentAlbum::ID)
		<x-icons.badge class='badge--visible badge--list' icon='clock' />
		@break
		@case(App\SmartAlbums\OnThisDayAlbum::ID)
		<x-icons.badge class='badge--visible badge--tag badge--list' icon='calendar' />
		@break
		@default
	@endswitch
	@if ($is_public)
		<x-icons.badge class='badge--visible {{ $is_link_required ? "badge--hidden" : "badge--not--hidden"}} icn-share' icon='eye' />
	@endif
	@if ($is_password_required)
		<x-icons.badge class='badge--visible' icon='lock-locked' />
	@endif
	@if ($is_tag_album == "1")
		<x-icons.badge class='badge--tag' icon='tag' />
	@endif
	@if ($has_cover_id)
		<x-icons.badge class='badge--cover icn-cover' icon='folder-cover' />
	@endif
</div>
@endif
@if ($has_subalbum)
<div class='album_counters absolute right-2 top-2 flex flex-row gap-1 justify-end text-right font-bold font-sans drop-shadow-md'>
	<a class="layers relative py-1 px-0">
		<x-icons.iconic icon="layers" class=" fill-white w-3 h-3" />
	{{-- <x-icons.badge class='badge--folder w-3 h-3' icon='layers' /> --}}
	</a>
</div>
@endif
</div>