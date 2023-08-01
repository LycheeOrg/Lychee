@props(['field', 'class' => ''])
@error($field)
<span class=" text-red-600 font-bold {{ $class }}">{{ $message }}</span>
@enderror
