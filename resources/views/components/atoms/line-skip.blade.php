@props(['head' => '','value'])
@if($value != '')
<tr><td>{{ $head }}</td><td><span>{{ $value }}</span></td></tr>
@endif