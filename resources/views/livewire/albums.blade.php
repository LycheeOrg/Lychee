@if (!$smartalbums && !$albums && !$shared_albums)
<div class='no_content fadeIn'>
	<svg class='iconic'><use xlink:href='#eye' /></svg>
	<p>{{ Lang::get('VIEW_NO_PUBLIC_ALBUMS') }}</p>
</div>
@else
<div class="content contentZoomIn">
	<!-- test comment-->
	@isset($smartalbums)
		<div class='divider'><h1>{{ Lang::get('SMART_ALBUMS') }}</h1></div>
		@foreach ($smartalbums as $data)
			@include('livewire.parts.album')
		@endforeach
		@if(count($albums) > 0)
		<div class='divider'><h1>{{ Lang::get('ALBUMS') }}</h1></div>
		@endif
	@endisset

	@if(count($albums) > 0)
		@foreach ($albums as $data)
			@include('livewire.parts.album')
		@endforeach
	@endif

	@if(count($shared_albums) > 0)
		<div class='divider'><h1>{{ Lang::get('SHARED_ALBUMS') }}</h1></div>
		@foreach ($shared_albums as $data)
			@include('livewire.parts.album')
		@endforeach
	@endif
</div>
@endif