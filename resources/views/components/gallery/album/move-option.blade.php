@props(['album', 'prefix' => ''])
<option class="text-neutral-800" value="{{ $album->id }}">
{{$prefix}}&nbsp;└&nbsp;<img class=" w-4 h-4 rounded-sm box-shadow box-border" src="{{ URL::asset($album->thumb?->thumbUrl ?? '') }}" />{{ $album->title }}
</option>
@foreach($album->children as $child)
<x-gallery.album.move-option :album="$child" prefix="{{$prefix}}&nbsp;&nbsp;" />
@endforeach