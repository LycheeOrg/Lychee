@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ $title }}
@endcomponent
@endslot

{{-- Body --}}
# New photos have been added!

@foreach ($photos as $album)

<div class="" style="margin-top:15px;">

<p>{{ $album['name'] }}:</p>

@foreach($album['photos'] as $key => $photo)
<div class="" style="display:inline-block;margin-right: 3px; margin-bottom: 3px;">
    <a href="{{ $photo['link'] }}"><img src="{{ $photo['thumb'] }}" alt="{{ $photo['title'] }}" style="max-width: 60px;" /></a>
</div>
@endforeach

</div>

@endforeach

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
<a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
@endcomponent
@endslot
@endcomponent
