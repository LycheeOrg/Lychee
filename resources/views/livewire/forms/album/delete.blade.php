<div class="text-neutral-200 text-sm p-9">
	<p class="mb-4">{{ sprintf(__('lychee.DELETE_ALBUM_CONFIRMATION'), $title) }}</p>
	{{-- TODO add confirmation by typing album name --}}
	<x-forms.buttons.danger class="rounded-md w-full" wire:click='delete'>{{ __('lychee.DELETE_ALBUM_QUESTION') }}</x-forms.buttons.danger>
</div>