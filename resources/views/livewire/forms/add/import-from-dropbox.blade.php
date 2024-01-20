<div x-data="dropboxView(@entangle('form.urlArea'), '{{ __('lychee.UPLOAD_IN_PROGRESS')}}')">
	@assets
	<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id='dropboxjs' data-app-key='{{ $this->api_key }}'></script>
	@endassets
	<div class="p-9">
		<p class="mb-5 text-text-main-200 text-sm/4">{{ __('lychee.IMPORT_DROPBOX') }}</p>
		<x-forms.buttons.action class="rounded-md w-full"
			x-bind:class="urlArea === '' ? '' : 'hidden'"
			x-on:click="chooseFromDropbox">Select from Dropbox</x-forms.buttons.action>
		<form>
			<div class="my-3 first:mt-0 last:mb-0"
				x-bind:class="urlArea === '' ? 'hidden' : ''">
				<x-forms.textarea
					class="w-full text-2xs !text-text-main-400" autocapitalize="off"
					x-model="urlArea" wire:model="form.urlArea"
					placeholder="https://&#10;https://&#10;..."
					:has_error="$errors->has('form.urls.*')" />
			</div>
		</form>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full"
			x-bind:disabled="urlArea === ''"
			x-bind:class="urlArea === '' ? '!cursor-not-allowed hover:!text-primary-500 hover:!bg-transparent' : ''"
			x-on:click="send()"
			@keydown.enter.window="send()"
			>{{ __('lychee.UPLOAD_IMPORT') }}</x-forms.buttons.action>
	</div>
</div>