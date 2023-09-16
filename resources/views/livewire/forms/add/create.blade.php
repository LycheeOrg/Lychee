<div>
    <div class="p-9">
        <p class="mb-5 text-neutral-200 text-sm/4">{{ __('lychee.TITLE_NEW_ALBUM') }}</p>
        <form>
            <div class="my-3 first:mt-0 last:mb-0">
                <x-forms.inputs.text class="w-full" autocapitalize="off" wire:model="title"
                    placeholder="{{ __('lychee.UNTITLED') }}" :has_error="$errors->has('title')" />
            </div>
        </form>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full"
            wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
        <x-forms.buttons.action class="border-t border-t-dark-800 rounded-br-md w-full"
            wire:click="submit">{{ __('lychee.CREATE_ALBUM') }}</x-forms.buttons.action>
    </div>
</div>
