@props([
	'head' => '',
	'valueTrue' => '',
	'valueFalse' => '',
	'value'])
<tr>
	<td>{{ $head }}</td>
	<td><span>
	{{ $value ? $valueTrue : $valueFalse }}
@endif
</span></td>
</tr>