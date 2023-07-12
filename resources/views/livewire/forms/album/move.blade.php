<div class="text-neutral-200 text-sm p-9 text-center max-w-3xl">
	<p class="mb-4 text-center">{{ __('lychee.ALBUM_MOVE') }}</p>
	<div class="mt-4 h-12">
		<span class="font-bold">{{ "Move to" }}</span>

		<span class="relative w-max my-[1px] text-white rounded overflow-hidden bg-black/30 inline-block text-2xs align-middle
		after:content-['≡'] after:absolute after:text-sky-400 after:right-2 after:top-0 after:font-bold after:text-lg after:-mt-1
		after:pointer-events-none mx-2">
			<select class="m-0 py-1 w-[120%] text-white bg-transparent text-2xs px-2" wire:model='albumID'>
			@foreach($this->albumList as $option)
				<x-gallery.album.move-option :album="$option" />
			@endforeach
			</select>
		</span>
	</div>
	<x-forms.buttons.danger class="rounded-md w-full" wire:click='move'>{{ "Transfer ownership of album and photos" }}</x-forms.buttons.danger>
</div>