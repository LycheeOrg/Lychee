<a wire:navigate href="{{ route('livewire-gallery-album', ['albumId' => $id]) }}"
	class="album-thumb block relative {{ $aspect_ratio_class }}
	w-[calc(33vw-9px-4px)] ml-1 mt-1
	sm:w-[calc(25vw-9px-10px)] sm:ml-2 sm:mt-2
	md:w-[calc(20vw-9px-18px)] md:ml-4 md:mt-4
	lg:w-[calc(16vw-9px-15px)] lg:ml-5 lg:mt-5
	xl:w-[calc(14vw-9px-22px)] xl:ml-6 xl:mt-6
	2xl:w-52 2xl:ml-7 2xl:mt-7
	animate-zoomIn group {{ $is_nsfw_blurred ? 'blurred' : '' }}
	"
	{{-- if it is NOT nsfw => display Otherwise check nsfwAlbumsVisible alpine value --}}
	{{-- This would be better if livewire did not add comments around @if --}}
	x-show="{{ !$is_nsfw ? 'true' : 'false' }} || albumFlags.areNsfwVisible"
	data-id='{{ $id }}'
	x-on:contextmenu.prevent="handleContextAlbum($event, $wire)"
	x-on:click='select.handleClickAlbum($event, $wire)'
	x-bind:class="select.selectedAlbums.includes('{{ $id }}') ? 'outline outline-1 outline-primary-500' : ''"
	>
	<x-gallery.album.thumbs.album-thumb class="group-hover:border-primary-500 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<x-gallery.album.thumbs.album-thumb class="group-hover:border-primary-500 group-hover:rotate-6 group-hover:translate-x-3  group-hover:-translate-y-2"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<x-gallery.album.thumbs.album-thumb class="group-hover:border-primary-500"
		type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	<div class="overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[calc(100%-2px)] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm {{ $css_overlay }}">
		<h1 class="w-full pt-3 pb-1 pr-1 pl-4 text-sm text-text-main-0 font-bold text-ellipsis whitespace-nowrap overflow-x-hidden"
			title='{{ $title }}'>{{ $title }}</h1>
		<span class="block mt-0 mr-0 mb-3 ml-4 text-2xs text-text-main-300">
			@switch($subType)
				@case('description')
					{{ Str::limit($description, 100) }}
					@break
				@case('takedate')
					@isset($max_taken_at)
					<x-icons.iconic icon="camera" class=" fill-neutral-200 w-3 h-3" /> {{ $max_taken_at === $min_taken_at ? $min_taken_at : $max_taken_at . ' - ' . $min_taken_at }}
					@break
					@endisset
				@case('creation')
					{{ $created_at }}
					@break
				@default
					{{ $max_taken_at === $min_taken_at ? $min_taken_at : $max_taken_at . ' - ' . $min_taken_at }}					
			@endswitch
		</span>
	</div>
	@if(Str::contains($thumb?->type, 'video'))
	<span class="w-full h-full absolute hover:opacity-70 transition-opacity duration-300">
		<img class="h-full w-full" alt="play"  src="{{ URL::asset("img/play-icon.png") }}" />
	</span>
	@endif
	@auth
	<div class='badges absolute mt-[-1px] ml-1 top-0 left-0'>
		@if ($is_nsfw)
			<x-gallery.badge class='badge--nsfw bg-[#ff82ee]' icon='warning' />
		@endif
		@switch($id)
			@case(App\SmartAlbums\StarredAlbum::ID)
			<x-gallery.badge class='badge--star bg-yellow-500' icon='star' />
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
		@if ($is_cover_id)
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