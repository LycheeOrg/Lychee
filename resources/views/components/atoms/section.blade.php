@props(['head'])
<div class="sidebar__divider">
	<h1>{{ $head }}</h1>
</div>
<table>
	<tbody>
		{{ $slot }}
	</tbody>
</table>