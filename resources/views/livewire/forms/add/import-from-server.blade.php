<div>
	<div class="p-9"
		x-data="{
			import_via_symlink: @entangle('form.import_via_symlink'),
			delete_imported: @entangle('form.delete_imported'),
			skip_duplicates: @entangle('form.skip_duplicates'),
			resync_metadata: @entangle('form.resync_metadata')
		}">
		<p class="mb-5 text-neutral-200 text-sm/4">{{ __('lychee.UPLOAD_IMPORT_SERVER_INSTR') }}</p>
		{{-- <div class="text-red-500 font-bold">@error('form.paths') {{ $message }} @enderror</div>
		<div class="text-red-500 font-bold">@error('form.import_via_symlink') {{ $message }} @enderror</div>
		<div class="text-red-500 font-bold">@error('form.delete_imported') {{ $message }} @enderror</div>
		<div class="text-red-500 font-bold">@error('form.skip_duplicates') {{ $message }} @enderror</div>
		<div class="text-red-500 font-bold">@error('form.resync_metadata') {{ $message }} @enderror</div> --}}
		<form>
			<div class="my-3 first:mt-0 last:mb-0">
				<x-forms.inputs.text class="w-full" autocapitalize="off" wire:model="form.path" :has_error="$errors->has('form.paths.*')" />
			</div>
			<div class='relative my-3 pl-9 transition-color duration-300' x-bind:class="import_via_symlink ? 'disabled' : 'text-neutral-200'">
				<label class="font-bold block " for="server_import_dialog_delete_imported_check">{{ __('lychee.UPLOAD_IMPORT_DELETE_ORIGINALS') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_delete_imported_check" x-model='delete_imported' x-bind:disabled="import_via_symlink" />
				<p>{{ __('lychee.UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL') }}</p>
			</div>
			<div class='relative my-3 pl-9 transition-color duration-300' x-bind:class="delete_imported ? 'disabled' : 'text-neutral-200'">
				<label class="font-bold block " for="server_import_dialog_symlink_check">{{ __('lychee.UPLOAD_IMPORT_VIA_SYMLINK') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_symlink_check" x-model='import_via_symlink' x-bind:disabled="delete_imported" />
				<p>{{ __('lychee.UPLOAD_IMPORT_VIA_SYMLINK_EXPL') }}</p>
			</div>
			<div class='relative my-3 pl-9 transition-color duration-300 text-neutral-200'>
				<label class="font-bold block " for="server_import_dialog_skip_check">{{ __('lychee.UPLOAD_IMPORT_SKIP_DUPLICATES') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_skip_check" x-model='skip_duplicates' />
				<p>{{ __('lychee.UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL') }}</p>
			</div>
			<div class='relative my-3 pl-9 transition-color duration-300' x-bind:class="skip_duplicates ? 'text-neutral-200' : 'disabled'">
				<label class="font-bold block " for="server_import_dialog_resync_check">{{ __('lychee.UPLOAD_IMPORT_RESYNC_METADATA') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_resync_check" x-model='resync_metadata' x-bind:disabled="!skip_duplicates"  />
				<p>{{ __('lychee.UPLOAD_IMPORT_RESYNC_METADATA_EXPL') }}</p>
			</div>
		</form>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-dark-800 rounded-br-md w-full"
			@keydown.enter.window="$wire.submit()"
			wire:click="submit">{{ __('lychee.UPLOAD_IMPORT') }}</x-forms.buttons.action>
	</div>
</div>