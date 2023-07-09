<div class="text-neutral-200 text-sm p-9 min-w-[32rem] flex-shrink-0">
	<form>
        <div class="mb-4 h-12">
            <p class="font-bold">{{ __('lychee.ALBUM_TITLE') }}:</p>
            <x-forms.inputs.text wire:model='title' />
        </div>
        <div class="my-4 h-56">
            <p class="font-bold">{{ __('lychee.ALBUM_DESCRIPTION') }}:</p>
            <x-forms.textarea class="w-full h-48" wire:model="description"></x-forms.textarea>
        </div>
        <div class="mt-4 h-12">
            <span class="font-bold">{{ __('lychee.ALBUM_ORDERING') }}</span>
            {{-- <label for="sorting_dialog_column_select">{{ __('lychee.SORT_DIALOG_ATTRIBUTE_LABEL') }}</label> --}}
            <x-forms.dropdown class="mx-2" :options="$sorting_columns" id="sorting_dialog_column_select" wire:model='sorting_column'/>
            {{-- <label for="sorting_dialog_order_select">{{ __('lychee.SORT_DIALOG_ORDER_LABEL') }}</label> --}}
            <x-forms.dropdown class="mx-2" :options="$sorting_orders" id="sorting_dialog_order_select" wire:model='sorting_order'/>
        </div>
        <x-forms.buttons.action class="rounded w-full" wire:click='submit' >{{ __('lychee.SAVE') }}</x-forms.buttons.action>
    </form>
</div>
