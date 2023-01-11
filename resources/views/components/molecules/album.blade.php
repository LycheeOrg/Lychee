<div wire:click="$emit('openAlbum', '{{ $id }}')" class='album {{ $is_nsfw ? 'blurred' : '' }}'
	{{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $is_nsfw ? '1' : '0'}}'>
	@for ($i = 0; $i < 3; $i++)
		<x-atoms.album.thumbimg type="{{ $thumb?->type ?? '' }}" thumb="{{ $thumb?->thumbUrl ?? '' }}" thumb2x="{{ $thumb?->thumb2xUrl ?? '' }}" />
	@endfor

<div class='overlay'>
	<h1 title='{{ $title }}'>{{ $title }}</h1>
	{{-- <a>{{ $data['date_stamp'] }}</a> --}}
</div>

@if (Auth::check())
<div class='badges'>
	@if ($is_nsfw)
		<x-icons.icon class='badge--nsfw icn-warning' icon='warning' />
	@endif
	@if ($id == App\SmartAlbums\StarredAlbum::ID)
		<x-icons.icon class='badge--star icn-star' icon='star' />
	@endif
	@if ($id == App\SmartAlbums\PublicAlbum::ID)
		<x-icons.icon class='badge--visible badge--hidden icn-share' icon='eye' />
	@endif
	@if ($id == App\SmartAlbums\UnsortedAlbum::ID)
		<x-icons.icon class='badge--visible' icon='list' />
	@endif
	@if ($id == App\SmartAlbums\RecentAlbum::ID)
		<x-icons.icon class='badge--visible badge--list' icon='clock' />
	@endif
	@if ($is_public)
		<x-icons.icon class='badge--visible {{ $is_link_required ? "badge--hidden" : "badge--not--hidden"}} icn-share' icon='eye' />
	@endif
	@if ($is_password_required)
		<x-icons.icon class='badge--visible' icon='lock-locked' />
	@endif
	@if ($is_tag_album == "1")
		<x-icons.icon class='badge--tag' icon='tag' />
	@endif
	@if ($has_cover_id)
		<x-icons.icon class='badge--cover icn-cover' icon='folder-cover' />
	@endif
</div>
@endif
@if ($has_subalbum)
<div class='subalbum_badge'>
	<x-icons.icon class='badge--folder' icon='layers' />
</div>
@endif
</div>