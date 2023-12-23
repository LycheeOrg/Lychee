@props(['icon', 'class' => '', 'icon_class'=> ''])
<div {{ $attributes }} class="my-1 pr-6 pl-10 pb-1 text-text-main-200 transition-all ease-in-out duration-300 cursor-pointer
hover:scale-105 hover:bg-gradient-to-b hover:from-primary-500 hover:to-primary-600 hover:text-text-hover
{{ $class }}">
	<span class="inline-block w-full text-sm leading-3" data-num="2">
		<x-icons.iconic icon="{{ $icon }}" class="w-3 h-3 -ml-5 mr-2 fill-white {{ $icon_class }}" />{{ $slot }}
	</span>
</div>