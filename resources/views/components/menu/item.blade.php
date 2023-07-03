@props(['action' => 'none','icon', 'icon_class'=> ''])
<div
	@if ($action !== 'none')
	wire:click='{{ $action }}'
	@endif
	class="basicContext__item ">
	<span class="basicContext__data"  style="display: inline-block; width: 100%;" data-num="2">
		<x-icons.iconic icon="{{ $icon }}" class="{{ $icon_class }}" />
		{{ $slot }}
	</span>
</div>