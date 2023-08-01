<div class="my-4">
	<p>
		{{ $description }}
		<x-forms.dropdown wire:model.live="value" :options="$this->options" />
	</p>
</div>