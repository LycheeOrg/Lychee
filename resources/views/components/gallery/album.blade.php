<div
	wire:click="$emit('openAlbum', '{{ $id }}')"
	class="relative w-52 h-52 mt-7 ml-7"
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


{{-- margin: 0 0 0 6px;
padding: 12px 8px 6px;
width: 18px;
background: #d92c34;
-webkit-box-shadow: 0 0 2px rgba(0,0,0,.6);
box-shadow: 0 0 2px rgba(0,0,0,.6);
border-radius: 0 0 5px 5px;
border: 1px solid #fff;
border-top: none;
color: #fff;
text-align: center;
text-shadow: 0 1px 0 rgba(0,0,0,.4);
opacity: .9;
 --}}
@if (Auth::check())
<div class='badges absolute mt-[-1px] ml-1'>
	@if ($is_nsfw)
		<x-icons.badge class='badge--nsfw inline-block bg-[#ff82ee]
		 ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='warning' />
	@endif
	@switch($id)
		@case(App\SmartAlbums\StarredAlbum::ID)
		<x-icons.badge class='badge--star inline-block bg-yellow-500
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='star' />
		@break
		@case(App\SmartAlbums\PublicAlbum::ID)
		<x-icons.badge class='badge--public inline-block badge--not--hidden bg-green-600
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='eye' />
		@break
		@case(App\SmartAlbums\UnsortedAlbum::ID)
		<x-icons.badge class='badge--unsorted inline-block bg-red-700
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='list' />
		@break
		@case(App\SmartAlbums\RecentAlbum::ID)
		<x-icons.badge class='badge--recent inline-block badge--list bg-blue-700
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='clock' />
		@break
		@case(App\SmartAlbums\OnThisDayAlbum::ID)
		<x-icons.badge class='badge--onthisday inline-block badge--tag badge--list bg-green-600
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='calendar' />
		@break
		@default
	@endswitch
	@if ($is_public)
		<x-icons.badge class='badge--ispublic inline-block {{ $is_link_required ? "bg-yellow-500" : "bg-green-600"}}
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='eye' />
	@endif
	@if ($is_password_required)
		<x-icons.badge class='badge--locked inline-block bg-orange-400
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='lock-locked' />
	@endif
	@if ($is_tag_album == "1")
		<x-icons.badge class='badge--tag inline-block bg-green-600
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='tag' />
	@endif
	@if ($has_cover_id)
		<x-icons.badge class='badge--cover inline-block bg-yellow-500
		ml-1 px-[6rem] pt-3 pb-2 w-[16px] border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center' classIcon='w-4 h-4 fill-white overflow-hidden' icon='folder-cover' />
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