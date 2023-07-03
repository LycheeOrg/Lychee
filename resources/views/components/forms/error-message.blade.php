@props(['field'])
@error($field)
<span style="color:red; font-weight:bold;">{{ $message }}</span>
@enderror
