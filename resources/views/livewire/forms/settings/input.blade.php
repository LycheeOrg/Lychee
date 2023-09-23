<div class="my-8">
	<p class="m-0 w-full text-neutral-400">
		{!! $description !!}
		<x-forms.inputs.text class="mt-2 w-full" placeholder="{{ $placeholder }}" wire:model="value" />
	</p>
	<div class="basicModal__buttons w-full">
		<x-forms.buttons.action class="rounded-md w-full" wire:click="save">{{ $action }}</x-forms.buttons.action>
	</div>
</div>
