<div class="u2f_view_line flex">
	<x-forms.inputs.text class="w-full mt-4" wire:model="alias" />
	<x-forms.buttons.danger wire:click="delete" class="w-1/4 rounded-r-md">{{ __('lychee.DELETE') }}</x-forms.buttons.danger>
</div>