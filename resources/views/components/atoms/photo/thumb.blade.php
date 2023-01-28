@props([
	'src' => '',
	'srcset' => '',
	'srcset2x' => '',
	'class' => '',
	'is_lazyload' => false])
<span class="thumbimg {{ $class }}">
	<img
	@if ($is_lazyload)
		class='lazyload'
	@else
		data-tabindex='{{ Helpers::data_index() }}'
	@endif

	{!! $src !!}
	{!! $srcset !!}
	{!! $srcset2x !!}

	alt='Photo thumbnail'
	data-overlay='false'
	draggable='false'
	/>
</span>