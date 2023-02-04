@props([
	'src' => '',
	'srcset' => '',
	'srcset2x' => '',
	'class' => '',
	'is_lazyload' => false])
<span class="thumbimg {{ $class }}">
	<img
		alt='Photo thumbnail'
	@if ($is_lazyload)
		class='lazyload'
	@else
		data-tabindex='{{ Helpers::data_index() }}'
	@endif

	{!! $src !!}
	{!! $srcset !!}
	{!! $srcset2x !!}

	data-overlay='false'
	draggable='false'
	/>
</span>