<div class="my-8">
	<p class="m-0 w-full text-neutral-200">
		{!! $description !!}
		<input class="
			w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
			hover:border-b-sky-400 focus:border-b-sky-400 shadow shadow-white/5
			text" wire:model="value" type="text" placeholder="{{ $placeholder }}">
	</p>
	<div class="basicModal__buttons w-full">
		<x-forms.buttons.action class="rounded-md" wire:click="save">{{ $action }}</x-forms.buttons.action>
	</div>
</div>
