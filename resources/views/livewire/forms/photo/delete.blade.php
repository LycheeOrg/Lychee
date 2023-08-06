<div>
	<div class="p-9">
		<p>{{ sprintf(__('lychee.PHOTO_DELETE_CONFIRMATION'), $title) }}</p>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.PHOTO_KEEP' ) }}</x-forms.buttons.cancel>
		<x-forms.buttons.danger class="border-t border-t-dark-800 rounded-br-md w-full" wire:click="submit">{{ __('lychee.PHOTO_DELETE') }}</x-forms.buttons.action>
	</div>
</div>