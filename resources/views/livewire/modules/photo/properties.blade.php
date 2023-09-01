<div id="lychee_sidebar" class="border-t border-solid border-sky-500 text-neutral-200 w-full flex justify-center">
    <form>
        <div
            class="xl:w-5/6 flex justify-center flex-wrap text-neutral-200 text-sm p-9 sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
            <div class="mb-4 h-12 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_NEW_TITLE') }}</p>
                <x-forms.inputs.text wire:model='title' />
                <x-forms.error-message field='title' />
            </div>
            <div class="my-4 h-56 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_NEW_DESCRIPTION') }}</p>
                <x-forms.textarea class="w-full h-52" wire:model="description"></x-forms.textarea>
                <x-forms.error-message field='description' />
            </div>
            <div class="my-4 h-12 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_NEW_TAGS') }}</p>
                <x-forms.inputs.text wire:model='tags_with_comma' />
                <x-forms.error-message field='tags' />
            </div>
            {{-- <div class="mt-4 h-12 w-full"> --}}
                {{-- <span class="font-bold">{{ __('lychee.ALBUM_ORDERING') }}</span>
                <x-forms.dropdown class="mx-2" :options="$this->photoSortingColumns" id="sorting_dialog_column_select" wire:model='sorting_column'/>
                <x-forms.dropdown class="mx-2" :options="$this->sortingOrders" id="sorting_dialog_order_select" wire:model='sorting_order'/> --}}
            {{-- </div> --}}
            <x-forms.buttons.action class="rounded w-full"
                wire:click='submit'>{{ __('lychee.SAVE') }}</x-forms.buttons.action>
        </div>
    </form>
</div>
