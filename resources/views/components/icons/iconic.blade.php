@props(['class' => '', 'icon'])
<svg class='inline-block fill-neutral-400 {{ $class }}' {{ $attributes }}><use xlink:href='#{{ $icon }}' /></svg>