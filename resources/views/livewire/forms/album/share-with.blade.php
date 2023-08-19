<div class="text-neutral-200 text-sm p-9 sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[40rem] flex-shrink-0">
    <div class="flex flex-col">
		@if(count($perms) > 0)
        <div class="w-full flex my-1">
            <p class="w-full flex align-middle">
                <span class="h-4 w-56 inline-block mt-2.5">{{ __('lychee.USERNAME') }}</span>
                <span class="h-4 w-56 inline-block text-center">
                    <x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="fullscreen-enter" />
                    <x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="data-transfer-download" />
                    <x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="data-transfer-upload" />
                    <x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="pencil" />
                    <x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="trash" />
                </span>
            </p>
        </div>
		@endif
        @foreach ($perms as $perm)
            <livewire:forms.album.share-with-line :$perm :key="$perm->id"  />
        @endforeach
    </div>
    <div class="mt-4" x-data="{ isSearchUserOpen: false }" x-on:click.away="isSearchUserOpen = false">
        <form>
			@if($username !== null)
			<p class="my-3 w-full">Select the rights granted: full photo access, download, upload, edit, and delete. </p>
			<div class="w-full flex my-1">
				<p class="w-full flex align-middle">
					<span class="h-4 w-56 inline-block mt-2.5"></span>
					<span class="h-4 w-56 inline-block text-center">
						<x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="fullscreen-enter" />
						<x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="data-transfer-download" />
						<x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="data-transfer-upload" />
						<x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="pencil" />
						<x-icons.iconic class=" text-neutral-200 px-2 w-8 h-8 mb-2" icon="trash" />
					</span>
				</p>
			</div>
			<span class="h-4 w-56 inline-block relative mt-2.5 cursor-pointer">{{ $username }} <a class="block absolute right-0 top-0"
				wire:click="clearUsername()">
				<x-icons.iconic class=" fill-red-800 w-4 h-4 mb-2" icon="x" /></a>
			</span>
			<span class="w-56 inline-block text-center">
				<x-forms.tickbox title="User can access picture in full size"  wire:model.live='grants_full_photo_access' />
				<x-forms.tickbox title="User can download the album/pictures" wire:model.live='grants_download' />
				<x-forms.tickbox title="User can add other pictures to the album" wire:model.live='grants_upload' />
				<x-forms.tickbox title="User can edit the album" wire:model.live='grants_edit' />
				<x-forms.tickbox title="User can delete content from the album" wire:model.live='grants_delete' />
			</span>
			<x-forms.buttons.create class="rounded w-20" wire:click='add'>{{ __('lychee.ADD') }}</x-forms.buttons.action>
			@else
			<p class="my-3 w-full">Select the users to share this album with: </p>
            <input wire:model.live.debounce.500ms="search" type="text"
                class="bg-neutral-800 text-sm rounded-full w-56 px-2 pl-8 py-1 focus:outline-none focus:shadow-outline
				placeholder:text-neutral-400"
                placeholder="Search (Press '/' to focus)" x-ref="search"
                @keydown.window="
				if (event.keyCode === 191) {
					event.preventDefault();
					$refs.search.focus();
				}"
                @focus="isSearchUserOpen = true"
				@keydown="isSearchUserOpen = true"
                @keydown.escape.window="isSearchUserOpen = false"
				@keydown.shift.tab="isSearchUserOpen = false">
				<div class="absolute top-0">
					<svg class="fill-current w-4 text-neutral-500 mt-2 ml-2" viewBox="0 0 24 24">
						<path d="M16.32 14.9l5.39 5.4a1 1 0 01-1.42 1.4l-5.38-5.38a8 8 0 111.41-1.41zM10 16a6 6 0 100-12 6 6 0 000 12z" />
					</svg>
				</div>
			<div wire:loading class="spinner top-0 right-0 mr-4 mt-3"></div>
            <div class="z-50 absolute bg-neutral-800 text-xs rounded w-56 mt-4" x-show.transition.opacity="isSearchUserOpen">
                @if (count($this->userList) > 0)
                    <ul class=" max-h-[50vh] overflow-y-auto">
                        @foreach ($this->userList as $result)
                            <li class="border-b border-neutral-700 cursor-pointer transition-all ease-in-out duration-300
							hover:bg-gradient-to-b hover:from-sky-500 hover:to-sky-600 hover:text-white"
                                wire:click="select('{{ $result['id'] }}','{{ $result['username'] }}')">
                                <a class="px-1 py-1 flex items-center"
                                    @if ($loop->last) @keydown.tab="isSearchUserOpen = false" @endif>
                                    <span class="ml-4 text-left">{{ $result['username'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-3 py-3">No results for "{{ $search }}"</div>
                @endif
            </div>
			@endif
        </form>
    </div>

    {{-- <x-forms.buttons.action class="rounded w-full" wire:click='submit'>{{ __('lychee.SAVE') }}</x-forms.buttons.action> --}}
</div>
