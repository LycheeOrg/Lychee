@props([
	'head' => '',
	'valueTrue' => '',
	'valueFalse' => '',
	'value'])
<tr>
	<td>{{ $head }}</td>
	<td><span>
@if (is_bool($value))
	{{ $value ? $valueTrue : $valueFalse }}
@else
	{{ $value }}
@endif
</span></td>
</tr>
