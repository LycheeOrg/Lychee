<div class="mt-4 h-10" x-data="{ isSearchPhotoOpen: false }" x-on:click.away="isSearchPhotoOpen = false">
	<span class="inline-block font-bold w-32">{{ __('lychee.SET_HEADER') }}</span>
	@if ($header_id !== null)
		<span class="h-4 inline-block relative px-2 cursor-pointer text-2xs">{{ $title ?? "Compact Header" }}
			<a class="block absolute right-0 top-0" wire:click="clearHeaderId()">
			<x-icons.iconic class=" fill-red-800 w-4 h-4 mb-2 translate-x-full" icon="x" /></a>
		</span>
	@else
		<span class="mx-2 w-full">
			<form>
				<input wire:model.live.debounce.500ms="search" type="text"
					class="my-[1px] text-text-main-0 rounded bg-black/30 inline-block text-2xs align-middle
					px-2 py-1 focus:outline-none focus:shadow-outline
					placeholder:text-text-main-400"
					placeholder="Search (Press '/' to focus)" x-ref="search"
					@keydown.window="
		if (event.key === '/') {
			event.preventDefault();
			$refs.search.focus();
		}"
					@focus="isSearchPhotoOpen = true" @keydown="isSearchPhotoOpen = true"
					@keydown.escape.window="isSearchPhotoOpen = false"
					@keydown.shift.tab="isSearchPhotoOpen = false">
			</form>
		</span>
		<div wire:loading class="spinner top-0 right-0 mr-4 mt-3"></div>
		<div class=" translate-x-[8.75rem] z-50 absolute bg-bg-900 text-xs rounded"
			x-show.transition.opacity="isSearchPhotoOpen">
			<ul class="max-h-[50vh] overflow-y-auto">
				@if(strlen( $search ?? '' ) === 0)
					<li class="border-b border-bg-800 cursor-pointer transition-all ease-in-out duration-300
								hover:bg-gradient-to-b hover:from-primary-500 hover:to-primary-600 hover:text-text-main-0"
						wire:click="select('{{ \App\Livewire\Components\Forms\Album\SetHeader::COMPACT_HEADER }}','{{ __('lychee.SET_COMPACT_HEADER') }}')">
						<a class="px-3 py-1 flex items-center">
							<x-icons.iconic class=" fill-white w-4 h-4 " icon="collapse-up" />
							<span class="ml-4 text-left">{{ __('lychee.SET_COMPACT_HEADER') }}</span>
						</a>
					</li>
				@endif
				@forelse ($this->photoList as $result)
					<li class="border-b border-bg-800 cursor-pointer transition-all ease-in-out duration-300
								hover:bg-gradient-to-b hover:from-primary-500 hover:to-primary-600 hover:text-text-main-0"
						wire:click="select('{{ $result['id'] }}',@js($result['title']))">
						<a class="px-3 py-1 flex items-center"
							@if ($loop->last) @keydown.tab="isSearchPhotoOpen = false" @endif>
							<img src="{{ $result['thumb'] }}" alt="poster" class=" w-4 rounded-sm">
							<span class="ml-4 text-left">{{ ($result['title']) }}</span>
						</a>
					</li>
				@empty
					@if(strlen( $search ?? '' ) > 0)
					<li class="border-b border-bg-800 cursor-pointer transition-all ease-in-out duration-300
								hover:bg-gradient-to-b hover:from-primary-500 hover:to-primary-600 hover:text-text-main-0">
						<a class="px-3 py-1 flex items-center">No results for "{{ $search }}"</a>
					</li>
					@endif
				@endforelse
			</ul>
		</div>
	@endif
</div>
