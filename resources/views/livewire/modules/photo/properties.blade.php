<div id="lychee_sidebar" class="border-t border-solid border-sky-500 text-neutral-200 w-full">
    <form class="w-full flex justify-center">
        <div
            class="xl:w-1/2 flex justify-center flex-wrap text-neutral-200 text-sm p-9 sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
            <div class="mb-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_TITLE') }}</p>
                <p class="text-neutral-400">{{ __('lychee.PHOTO_NEW_TITLE') }}</p>
                <x-forms.inputs.text wire:model='title' />
                <x-forms.error-message field='title' />
            </div>
            <div class="my-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_DESCRIPTION') }}</p>
                <p class="text-neutral-400">{{ __('lychee.PHOTO_NEW_DESCRIPTION') }}</p>
                <x-forms.textarea class="w-full h-52" wire:model="description"></x-forms.textarea>
                <x-forms.error-message field='description' />
            </div>
            <div class="my-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_TAGS')}}</p>
                <p class="text-neutral-400">{{ __('lychee.PHOTO_NEW_TAGS') }}</p>
                <x-forms.inputs.text wire:model='tags_with_comma' />
                <x-forms.error-message field='tags' />
            </div>
            <div class="my-4 w-full">
                <p class="font-bold">{{ __('lychee.PHOTO_SET_CREATED_AT') }}</p>
                <p class="text-neutral-400">{{ __('lychee.PHOTO_NEW_CREATED_AT') }}</p>
                <x-forms.inputs.date wire:model='created_at' />
            </div>
            <div class="my-4 w-full">
                <p><span class="font-bold">{{ __('lychee.SET_LICENSE') }}</span>
                <x-forms.dropdown class="mx-2" :options="$this->licenses" id="licenses_dialog_select" wire:model='license'/>
                </p>
            </div>
            <x-forms.buttons.action class="rounded w-full"
                wire:click='submit'>{{ __('lychee.SAVE') }}</x-forms.buttons.action>
        </div>
    </form>
</div>
