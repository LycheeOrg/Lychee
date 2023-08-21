<div class="u2f_view_line flex">
	<x-forms.inputs.text class="w-full mt-4" wire:model.live.debounce.500ms="alias" :$has_error />
	<x-forms.buttons.danger wire:click="$parent.delete('{{ $credential->id }}')" class="w-1/4 rounded-r-md">{{ __('lychee.DELETE') }}</x-forms.buttons.danger>
</div>