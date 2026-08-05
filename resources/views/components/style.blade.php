@if(count($palettes) > 0)
<style>
	:root {
		@foreach ($palettes as $token => $palette)
			@foreach ($palette as $shade => $value)
				--ui-color-{{ $token }}-{{ $shade }}: {{ $value }};
			@endforeach
		@endforeach
	}
</style>
@endif