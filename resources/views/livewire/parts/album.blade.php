<div wire:click="$emit('openAlbum', '{{ $data['id'] }}')" class='album {{ $data['nsfw'] === "1" && App\Models\Configs::getValueAsBool('nsfw_blur', true) ? 'blurred' : '' }}'
	{{-- {{ $disabled ? 'disabled' : '' }} --}}
	data-id='{{ $data['id'] }}'
	data-tabindex='{{ Helpers::data_index() }}'
	data-nsfw='{{ $data['nsfw'] == "1" ? '1' : '0'}}'>
	@for ($i = 0; $i < 3; $i++)
		@if($data['thumb'])
			<x-album.thumbimg type="{{ $data['thumb']['type'] }}" thumb="{{ $data['thumb']['thumb'] }}" thumb2x="{{ $data['thumb']['thumb2x'] }}" />
		@else
			@include('components.album.thumb-placeholder')
		@endif
	@endfor

<div class='overlay'>
	<h1 title='{{ $data['title'] }}'>{{ $data['title'] }}</h1>
	{{-- <a>{{ $data['date_stamp'] }}</a> --}}
</div>

@if (Auth::check())
<div class='badges'>
	@if (isset($data['nsfw']) && $data['nsfw'] == "1")
		<x-icon class='badge--nsfw icn-warning' icon='warning' />
	@endif
	@if (isset($data['star']) && $data['star'] == "1")
		<x-icon class='badge--star icn-star' icon='star' />
	@endif
	@if (isset($data['public']) && $data['public'] == "1")
		<x-icon class='badge--visible {{ $data['visible'] == "1" ? "badge--not--hidden" : "badge--hidden"}} icn-share' icon='eye' />
	@endif
	@if (isset($data['unsorted']) && $data['unsorted'] == "1")
		<x-icon class='badge--visible' icon='list' />
	@endif
	@if (isset($data['recent']) && $data['recent'] == "1")
		<x-icon class='badge--visible badge--list' icon='clock' />
	@endif
	@if (isset($data['password']) && $data['password'] == "1")
		<x-icon class='badge--visible' icon='lock-locked' />
	@endif
	@if (isset($data['tag_album']) && $data['tag_album'] == "1")
		<x-icon class='badge--tag' icon='tag' />
	@endif
	@if (isset($data['cover_id']) && isset($data['thumb']['id']) && $data['cover_id'] == $data['thumb']['id'])
		<x-icon class='badge--cover icn-cover' icon='folder-cover' />
	@endif
</div>
@endif

@if ((isset($data['num_albums']) && $data['num_albums'] > 0) || (isset($data['albums']) && count($data['albums']) > 0))
<div class='subalbum_badge'>
	<x-icon class='badge--folder' icon='folder' />
	@if ((isset($data['show_num_albums']) && $data['show_num_albums'] == "1") && ((isset($data['num_albums']) && $data['num_albums'] > 1) || (isset($data['albums']) && count($data['albums']) > 1)))
		<span>{{ $data['num_albums'] }}</span>
	@endif
</div>
@endif
</div>
