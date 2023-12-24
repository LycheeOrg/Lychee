<div class="my-7">
	<p class="pb-2">
		{{ $description }}
		<x-forms.toggle wire:model.live="flag" />
	</p>
	@if($footer !== '')
	<p>
		{!! $footer !!}
	</p>
	@endif
</div>
