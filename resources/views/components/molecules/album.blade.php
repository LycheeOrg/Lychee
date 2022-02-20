<div wire:click="$emit('openAlbum', '{{ $id }}')" class='album {{ $is_nsfw ? 'blurred' : '' }}'
	{{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $is_nsfw ? '1' : '0'}}'>
	@for ($i = 0; $i < 3; $i++)
		@if($thumb)
			<x-album.thumbimg type="{{ $thumb->type }}" thumb="{{ $thumb->thumbUrl }}" thumb2x="{{ $thumb->thumb2xUrl }}" />
		@else
			@include('components.atoms.album.thumb-placeholder')
		@endif
	@endfor

<div class='overlay'>
	<h1 title='{{ $title }}'>{{ $title }}</h1>
	{{-- <a>{{ $data['date_stamp'] }}</a> --}}
</div>

@if (AccessControl::is_logged_in())
<div class='badges'>
	@if ($is_nsfw)
		<x-icon class='badge--nsfw icn-warning' icon='warning' />
	@endif
	@if ($data->id == App\SmartAlbums\StarredAlbum::ID)
		<x-icon class='badge--star icn-star' icon='star' />
	@endif
	@if ($data->id == App\SmartAlbums\PublicAlbum::ID)
		<x-icon class='badge--visible badge--hidden icn-share' icon='eye' />
	@endif
	@if ($data->id == App\SmartAlbums\UnsortedAlbum::ID)
		<x-icon class='badge--visible' icon='list' />
	@endif
	@if ($data->id == App\SmartAlbums\RecentAlbum::ID)
		<x-icon class='badge--visible badge--list' icon='clock' />
	@endif
	@if ($is_public)
		<x-icon class='badge--visible {{ $requires_link ? "badge--hidden" : "badge--not--hidden"}} icn-share' icon='eye' />
	@endif
	@if ($has_password)
		<x-icon class='badge--visible' icon='lock-locked' />
	@endif
	@if ($is_tag_album == "1")
		<x-icon class='badge--tag' icon='tag' />
	@endif
	@if ($has_cover_id)
		<x-icon class='badge--cover icn-cover' icon='folder-cover' />
	@endif
</div>
@endif
@if ($has_subalbum)
<div class='subalbum_badge'>
	<x-icon class='badge--folder' icon='layers' />
</div>
@endif
</div>