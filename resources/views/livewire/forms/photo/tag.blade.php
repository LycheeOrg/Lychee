<div>
    <div class="p-9">
        <p class="mb-5 text-neutral-200 text-sm/4">
            {{ count($photoIDs) === 1 ? __('lychee.PHOTO_NEW_TAGS') : sprintf(__('lychee.PHOTOS_NEW_TAGS'), count($photoIDs)) }}
        </p>
        <form>
            <div class="my-3 first:mt-0 last:mb-0">
                <x-forms.inputs.text class="w-full" autocapitalize="off" wire:model="tag"
                    placeholder="{{ __('lychee.NO_TAGS') }}" :has_error="$errors->has('tags')" />
            </div>
            <div class='relative h-12 my-4 pl-9 transition-color duration-300'>
                <label class="block text-neutral-200"
                    for="pp_shall_override_check">{{ __('lychee.TAGS_OVERRIDE_INFO') }}</label>
                <x-forms.defaulttickbox id="pp_shall_override_check" wire:model='shall_override' />
            </div>

        </form>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full"
            wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
        <x-forms.buttons.action class="border-t border-t-dark-800 rounded-br-md w-full"
            @keydown.enter.window="$wire.submit()"
            wire:click="submit">{{ __('lychee.PHOTO_SET_TAGS') }}</x-forms.buttons.action>
    </div>
</div>
