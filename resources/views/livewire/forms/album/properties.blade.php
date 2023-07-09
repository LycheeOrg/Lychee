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
    </form>
</div>
