@props(['field', 'class' => ''])
@error($field)
<span class=" text-danger-600 font-bold {{ $class }}">{{ $message }}</span>
@enderror
