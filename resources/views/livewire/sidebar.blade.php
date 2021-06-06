<!-- Sidebar -->
<div class="sidebar active">
	<div class="sidebar__header">
		<h1>{{ Lang::get('ALBUM_ABOUT') }}</h1>
	</div>
	<div class="sidebar__wrapper">
	@foreach ($data as $section)
	<div class="sidebar__divider">
		<h1>{{ $section->title }}</h1>
	</div>
	<table>
		<tbody>
		@foreach ($section->content as $line)
		<tr>
			<td>{{ $line['head'] }}</td>
			<td><span>{{ $line['value'] }}</span></td>
		</tr>
		@endforeach
	</tbody>
	</table>
	@endforeach
	</div>
</div>