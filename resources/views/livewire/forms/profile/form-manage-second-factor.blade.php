<div class="u2f_view_line flex">
	<input class="text w-full pt-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
	hover:border-b-sky-400 focus:border-b-sky-400 shadow shadow-white/5" wire:model="alias" type="text">
	<a wire:click="delete" class="w-1/4 cursor-pointer transition-colors ease-in-out inline-block text-center pt-3 pb-4 font-bold text-red-800 rounded-r-md hover:text-white hover:bg-red-800">{{ __('lychee.DELETE') }}</a>
</div>