<div class="my-4">
	<p>
		{{ $description }}
		<x-forms.dropdown wire:model="value" :options="$this->options" />
	</p>
</div>