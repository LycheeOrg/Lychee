<div wire:click="$emit('openAlbum', '{{ $id }}')" class='album {{ $is_nsfw_blurred ? 'blurred' : '' }}'
	{{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $is_nsfw ? '1' : '0'}}'>
	@for ($i = 0; $i < 3; $i++)
		<x-gallery.album-thumb type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	@endfor

<div class='overlay'>
	<h1 title='{{ $title }}'>{{ $title }}</h1>
	<a>{{ $data['created_at'] ?? '' }}</a>
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
<div class='subalbum_badge'>
	<x-icons.badge class='badge--folder' icon='layers' />
</div>
@endif
</div>