<!-- Content -->
<div class="content">
@isset($smartalbums)
<div class='divider'><h1>{{ }}</h1></div>
@foreach ($smartalbums as $data)
	@include('livewire.parts.album')
@endforeach
@endisset

@foreach ($albums as $data)
	@include('livewire.parts.album')
@endforeach

@foreach ($shared_albums as $data)
	@include('livewire.parts.album')
@endforeach
</div>
