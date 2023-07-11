<div>
	<div class="p-9">
		<p class="text-red-700 text-xl font-bold capitalize text-center">
			{{ __('lychee.SETTINGS_ADVANCED_SAVE') }}
		</p>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.danger class="border-t border-t-dark-800 rounded-br-md w-full" wire:click="confirm">{{ __('lychee.CONFIRM') }}</x-forms.buttons.action>
	</div>
</div>