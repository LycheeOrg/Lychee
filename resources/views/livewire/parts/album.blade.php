<div class='album
	{{-- {{ $disabled ? 'disabled' : '' }} --}}
	{{ $data['nsfw'] === "1" && lychee.nsfw_blur ? 'blurred' : '' }}'
	data-id='{{ $data['id'] }}'
	data-nsfw='{{ $data['nsfw'] == "1" ? '1' : '0'}}'>
  {{-- ${build.getAlbumThumb(data, 2)}
  ${build.getAlbumThumb(data, 1)}
  ${build.getAlbumThumb(data, 0)} --}}

<div class='overlay'>
	<h1 title='{{ $data['title'] }}'>{{ $data['title'] }}</h1>
	{{-- <a>{{ $data['date_stamp'] }}</a> --}}
</div>
<svg class='iconic'><use xlink:href='#${icon}' /></svg>
<div class='badges'>
	@if (isset($data['nsfw']) && $data['nsfw'] == "1")
		<a class='badge badge--nsfw icn-warning'><svg class='iconic'><use xlink:href='#warning' /></svg></a>
	@endif
	@if (isset($data['star']) && $data['star'] == "1")
		<a class='badge badge--star icn-star'><svg class='iconic'><use xlink:href='#star' /></svg></a>
	@endif
	@if (isset($data['public']) && $data['public'] == "1")
		<a class='badge badge--visible {{ $data['visible'] == "1" ? "badge--not--hidden" : "badge--hidden"}} icn-share'>
			<svg class='iconic'><use xlink:href='#eye' /></svg></a>
	@endif
	@if (isset($data['unsorted']) && $data['unsorted'] == "1")
		<a class='badge badge--visible'><svg class='iconic'><use xlink:href='#list' /></svg></a>
	@endif
	@if (isset($data['recent']) && $data['recent'] == "1")
		<a class='badge badge--visible badge--list'><svg class='iconic'><use xlink:href='#clock' /></svg></a>
	@endif
	@if (isset($data['password']) && $data['password'] == "1")
		<a class='badge badge--visible'><svg class='iconic'><use xlink:href='#lock-locked' /></svg></a>
	@endif
	@if (isset($data['tag_album']) && $data['tag_album'] == "1")
		<a class='badge badge--tag'><svg class='iconic'><use xlink:href='#tag' /></svg></a>
	@endif
</div>

<div class='subalbum_badge'>
	<a class='badge badge--folder'><svg class='iconic'><use xlink:href='#layers' /></svg></a>
</div>

</div>