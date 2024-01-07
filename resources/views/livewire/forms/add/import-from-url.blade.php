<div>
	<div class="p-9" >
		<p class="mb-5 text-text-main-200 text-sm/4">{{ __('lychee.UPLOAD_IMPORT_INSTR') }}</p>
		<form>
			<div class="my-3 first:mt-0 last:mb-0">
				<x-forms.inputs.text class="w-full" x-intersect="$el.focus()" autocapitalize="off" wire:model="form.url" placeholder="https://" :has_error="$errors->has('form.urls.*')" />
			</div>
		</form>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full"
			@keydown.enter.window="$wire.submit()"
			wire:click="submit">{{ __('lychee.UPLOAD_IMPORT') }}</x-forms.buttons.action>
	</div>
</div>