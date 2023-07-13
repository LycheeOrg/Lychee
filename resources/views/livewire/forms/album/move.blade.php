<div class="text-neutral-200 text-sm p-9 text-center max-w-3xl border-red-500 border border-solid" x-data="{ isOpen: true }" @click.away="isOpen = false">
	<p class="mb-4 text-center">{{ __('lychee.ALBUM_MOVE') }}</p>
	<div class="mt-4 h-12">
		<div>
			<span class="font-bold">{{ "Move to" }}</span>
		</div>
		@if (strlen($search) < 2)
		<div>
			<span class="relative w-max my-[1px] text-white rounded overflow-hidden bg-black/30 inline-block text-2xs align-middle
			after:content-['≡'] after:absolute after:text-sky-400 after:right-2 after:top-0 after:font-bold after:text-lg after:-mt-1
			after:pointer-events-none mx-2">
				<select class="m-0 py-1 w-[120%] text-white bg-transparent text-2xs px-2" wire:model='albumID'>
				@foreach($this->albumListSaved as $album)
					<option class="text-neutral-800" value="{{ $album['id'] }}">
						{{ $album['title'] }}
					</option>
				@endforeach
				</select>
			</span>
		</div>
		@endif
		<div class="relative mt-3 w-full" >
			<input
				wire:model.debounce.500ms="search"
				type="text"
				class="bg-gray-800 text-sm rounded-full w-full px-4 pl-8 py-1 focus:outline-none focus:shadow-outline"
				placeholder="Search (Press '/' to focus)"
				x-ref="search"
				@keydown.window="
					if (event.keyCode === 191) {
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
				<svg class="fill-current w-4 text-gray-500 mt-2 ml-2" viewBox="0 0 24 24">
					<path d="M16.32 14.9l5.39 5.4a1 1 0 01-1.42 1.4l-5.38-5.38a8 8 0 111.41-1.41zM10 16a6 6 0 100-12 6 6 0 000 12z"/>
				</svg>
			</div>
		
			<div wire:loading class="spinner top-0 right-0 mr-4 mt-3"></div>
		
			@if (strlen($search) >= 2)
				<div
					class="z-50 absolute bg-gray-800 text-sm rounded w-full mt-4"
					x-show.transition.opacity="isOpen"
				>
					@if ($this->albumList->count() > 0)
						<ul>
							@foreach ($this->albumList as $result)
								<li class="border-b border-gray-700">
									<a class="block hover:bg-gray-700 px-3 py-3 flex items-center transition ease-in-out duration-150"
										@if ($loop->last) @keydown.tab="isOpen = false" @endif
									>
									{{-- @if ($result['poster_path'])
										<img src="https://image.tmdb.org/t/p/w92/{{ $result['poster_path'] }}" alt="poster" class="w-8">
									@else
										<img src="https://via.placeholder.com/50x75" alt="poster" class="w-8"> --}}
									{{-- @endif --}}
									<span class="ml-4">{{ $result['title'] }}</span>
								</a>
								</li>
							@endforeach
						</ul>
					@else
						<div class="px-3 py-3">No results for "{{ $search }}"</div>
					@endif
				</div>
			@endif
		</div>
	</div>
	<x-forms.buttons.danger class="rounded-md w-full" wire:click='move'>{{ "Transfer ownership of album and photos" }}</x-forms.buttons.danger>
</div>