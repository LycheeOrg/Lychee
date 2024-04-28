<div>
	<div class="p-9">
		<p class="mb-5 text-text-main-200">{{ $title }}</p>
		<form>
		@foreach($form as $f => $v)
			{{-- Only display the allowed forms --}}
			@if(!in_array($f, $formHidden, true))
				@if (is_string($f))
					<div class="my-3 first:mt-0 last:mb-0">
						<x-forms.inputs.text
						:has_error="$errors->has('form.' . $f)"
						autocomplete="on" placeholder="{{ $formLocale[$f] }}"
						autocapitalize="off"
						wire:model="form.{{ $f }}"
						/>
						<x-forms.error-message :field="'form.' . $f" />
					</div>
				@else
					{{ $f }} not supported yet.
				@endif
			@endif
		@endforeach
		</form>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full" wire:click="close">{{ $cancel }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full" wire:click="submit">{{ $validate }}</x-forms.buttons.action>
	</div>
</div>