@props(['class' => '', 'fill' => 'fill-neutral-400', 'icon'])
<svg class='inline-block {{ $fill }} {{ $class }}' {{ $attributes }}><use xlink:href='#{{ $icon }}' /></svg>