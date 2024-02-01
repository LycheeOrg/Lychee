<div class="text-text-main-200 text-sm p-9 max-w-3xl">
	<p class="mb-4 text-center">{{ sprintf(__('lychee.DELETE_ALBUM_CONFIRMATION'), $title) }}</p>
	<x-forms.buttons.danger class="rounded-md w-full" wire:click='delete'>
		{{ __('lychee.DELETE_ALBUM_QUESTION') }}
	</x-forms.buttons.danger>
</div>