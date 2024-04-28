<div class="relative mt-3 w-full"  x-data="{ isOpen: true }" x-on:click.away="isOpen = false">
	<input
		wire:model.live.debounce.500ms="search"
		type="text"
		class="bg-neutral-800 text-sm rounded-full w-full px-4 pl-8 py-1 focus:outline-none focus:shadow-outline
			placeholder:text-text-main-400"
		placeholder="Search (Press '/' to focus)"
		x-ref="search"
		@keydown.window="
			if (event.key === '/') {
				event.preventDefault();
				$refs.search.focus();
			}
		"
		@focus="isOpen = true"
		@keydown="isOpen = true"
		@keydown.escape.window="isOpen = false"
		@keydown.shift.tab="isOpen = false"
	>
	<div class="absolute top-0">
		<svg class="fill-current w-4 text-text-main-400 mt-2 ml-2" viewBox="0 0 24 24">
			<path d="M16.32 14.9l5.39 5.4a1 1 0 01-1.42 1.4l-5.38-5.38a8 8 0 111.41-1.41zM10 16a6 6 0 100-12 6 6 0 000 12z"/>
		</svg>
	</div>

	<div wire:loading class="spinner top-0 right-0 mr-4 mt-3"></div>

	<div class="z-50 absolute bg-neutral-800 text-xs rounded w-full mt-4"
		x-show.transition.opacity="isOpen">
		@if ($this->albumList->count() > 0)
			<ul class=" max-h-[50vh] overflow-y-auto">
				@foreach ($this->albumList as $result)
					<li class="border-b border-neutral-700 cursor-pointer transition-all ease-in-out duration-300
						hover:bg-gradient-to-b hover:from-primary-500 hover:to-primary-600 hover:text-text-main-0"
						wire:click="$parent.setAlbum('{{ $result['id'] }}', '{{ $result['original'] }}')">
						<a class="px-3 py-1 flex items-center"
							@if ($loop->last) @keydown.tab="isOpen = false" @endif
						>
						<img src="{{ $result['thumb'] }}" alt="poster" class=" w-4 rounded-sm">
						<span class="ml-4 text-left">{{ $result['short_title'] }}</span>
					</a>
					</li>
				@endforeach
			</ul>
		@else
			<div class="px-3 py-3">No results for "{{ $search }}"</div>
		@endif
	</div>
</div>
