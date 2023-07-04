<div class="my-8">
	<p class="m-0 w-full text-neutral-200">
		{!! $description !!}
		<input class="
			w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 outline-0 bg-transparent placeholder:text-neutral-500
			hover:border-b-teal-400 shadow shadow-white/5
			text" wire:model="value" type="text" placeholder="{{ $placeholder }}">
	</p>
	<div class="basicModal__buttons w-full">
		<a wire:click="save" class="basicModal__button cursor-pointer transition-colors ease-in-out w-full inline-block text-center pt-3 pb-4 font-bold text-teal-400 rounded-md hover:text-white hover:bg-teal-400">{{ $action }}</a>
	</div>
</div>
