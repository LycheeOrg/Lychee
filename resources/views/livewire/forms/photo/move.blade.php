<div>
	<div class="p-9">
		<p>{{ sprintf("Move %s to:", $title) }}</p>
		<livewire:forms.album.search-album lazy :parent_id="$parent_id" />
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL' ) }}</x-forms.buttons.cancel>
		{{-- <x-forms.buttons.danger class="border-t border-t-dark-800 rounded-br-md w-full" wire:click="submit">{{ __('lychee.CANCEL') }}</x-forms.buttons.action> --}}
	</div>
</div>