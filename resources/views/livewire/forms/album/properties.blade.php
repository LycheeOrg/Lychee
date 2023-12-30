<div class="text-neutral-200 text-sm p-9 sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
	<form>
        <div class="mb-4 h-12">
            <p class="font-bold">{{ __('lychee.ALBUM_TITLE') }}:</p>
            <x-forms.inputs.text wire:model='title' id="albumTitle" />
            <x-forms.error-message field='title' />
        </div>
        <div class="my-4 h-56">
            <p class="font-bold">{{ __('lychee.ALBUM_DESCRIPTION') }}:</p>
            <x-forms.textarea class="w-full h-48" wire:model="description"  id="albumDescription"></x-forms.textarea>
            <x-forms.error-message field='description' />
        </div>
        <div class="mt-4 h-10">
            <span class="font-bold">{{ __('lychee.ALBUM_PHOTO_ORDERING') }}</span>
            <x-forms.dropdown class="mx-2" :options="$this->photoSortingColumns" id="sorting_dialog_photo_column_select" wire:model='photo_sorting_column'/>
            <x-forms.dropdown class="mx-2" :options="$this->sortingOrders" id="sorting_dialog_photo_order_select" wire:model='photo_sorting_order'/>
        </div>
        @if($is_model_album)
        <div class="mb-4 h-10">
            <span class="font-bold">{{ __('lychee.ALBUM_CHILDREN_ORDERING') }}</span>
            <x-forms.dropdown class="mx-2" :options="$this->albumSortingColumns" id="sorting_dialog_album_column_select" wire:model='album_sorting_column'/>
            <x-forms.dropdown class="mx-2" :options="$this->sortingOrders" id="sorting_dialog_album_order_select" wire:model='album_sorting_order'/>
        </div>
        <div class="h-10">
            <span class="font-bold">{{ __('lychee.ALBUM_SET_LICENSE') }}</span>
            <x-forms.dropdown class="mx-2" :options="$this->licenses" id="license_dialog_select" wire:model='license'/>
        </div>
        @endif
        <x-forms.buttons.action class="rounded w-full" wire:click='submit' >
            {{ __('lychee.SAVE') }}
        </x-forms.buttons.action>
    </form>
</div>
