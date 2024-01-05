<div class="text-text-main-200 text-sm p-9 text-center w-full max-w-3xl min-h-[16rem]">
	@if ($title !== null && $title !== '')
	<p class="mb-4 text-center">{{ sprintf(__('lychee.ALBUM_MOVE'), $titleMoved, $title) }}</p>
	<x-forms.buttons.action class="rounded-md w-full" wire:click='move'>
		{{ __('lychee.MOVE_ALBUM') }}
	</x-forms.buttons.action>
	@else
	<div class="w-full">
		<div class="w-full">
			<span class="font-bold">{{ "Move to" }}</span>
		</div>
		<livewire:forms.album.search-album lazy :parent_id="$parent_id" :lft="$lft" :rgt="$rgt" />
	</div>
	@endif
</div>