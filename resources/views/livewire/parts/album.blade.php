{{-- @php
dd($data)
@endphp --}}
<div wire:click="$emit('openAlbum', '{{ $data->id }}')" class='album {{ isset($data->is_nsfw) && $data->is_nsfw && App\Models\Configs::get_value('nsfw_blur', '1') == '1' ? 'blurred' : '' }}'
	{{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $data->id }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ isset($data->is_nsfw) && $data->is_nsfw ? '1' : '0'}}'>
	@for ($i = 0; $i < 3; $i++)
		@if($data->thumb)
			<x-album.thumbimg type="{{ $data->thumb->type }}" thumb="{{ $data->thumb->thumbUrl }}" thumb2x="{{ $data->thumb->thumb2xUrl }}" />
		@else
			@include('components.album.thumb-placeholder')
		@endif
	@endfor

<div class='overlay'>
	<h1 title='{{ $data->title }}'>{{ $data->title }}</h1>
	{{-- <a>{{ $data['date_stamp'] }}</a> --}}
</div>

@if (AccessControl::is_logged_in())
<div class='badges'>
	@if (isset($data->is_nsfw) && $data->is_nsfw)
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
	@if (isset($data->is_public) && $data->is_public)
		<x-icon class='badge--visible {{ $data->requires_link ? "badge--hidden" : "badge--not--hidden"}} icn-share' icon='eye' />
	@endif
	@if (isset($data->has_password) && $data->has_password)
		<x-icon class='badge--visible' icon='lock-locked' />
	@endif
	@if (isset($data->tag_album) && $data->tag_album == "1")
		<x-icon class='badge--tag' icon='tag' />
	@endif
	@if (isset($data->cover_id) && isset($data->thumb['id']) && $data->cover_id == $data->thumb['id'])
		<x-icon class='badge--cover icn-cover' icon='folder-cover' />
	@endif
</div>
@endif
@php
	DebugBar::warning($data);
@endphp
@if ((isset($data->has_albums) && !$data->has_albums) || (isset($data->albums) && $data->albums->count() > 0) || (isset($data->_lft) && $data->_lft + 1 < $data->_rgt))
<div class='subalbum_badge'>
	<x-icon class='badge--folder' icon='layers' />
</div>
@endif
</div>