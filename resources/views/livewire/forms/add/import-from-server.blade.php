<div>
	<div class="p-9"
		x-data=>
		<p class="mb-5 text-neutral-200 text-sm/4">{{ __('lychee.UPLOAD_IMPORT_SERVER_INSTR') }}</p>
		<form>
			<div class="my-3 first:mt-0 last:mb-0">
				<x-forms.inputs.text class="w-full"
					data-tabindex="{{ Helpers::data_index() }}"
					placeholder="{{ __('lychee.USERNAME') }}"
					autocapitalize="off"
					wire:model="form.paths" />
			</div>
			<div @class(['relative my-3 pl-9 transition-color duration-300', 
				'disabled'=> $form['import_via_symlink'] === true,
				'text-neutral-200' => $form['import_via_symlink'] === false ])>
				<label class="font-bold block " for="server_import_dialog_delete_imported_check">{{ __('lychee.UPLOAD_IMPORT_DELETE_ORIGINALS') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_delete_imported_check" wire:model.live='form.delete_imported' :disabled="$form['import_via_symlink'] === true" />
				<p>{{ __('lychee.UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL') }}</p>
			</div>
			<div @class(['relative my-3 pl-9 transition-color duration-300', 
				'disabled'=> $form['delete_imported'] === true,
				'text-neutral-200' => $form['delete_imported'] === false ])>
				<label class="font-bold block " for="server_import_dialog_symlink_check">{{ __('lychee.UPLOAD_IMPORT_VIA_SYMLINK') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_symlink_check" wire:model.live='form.import_via_symlink' :disabled="$form['delete_imported'] === true" />
				<p>{{ __('lychee.UPLOAD_IMPORT_VIA_SYMLINK_EXPL') }}</p>
			</div>
			<div class='relative my-3 pl-9 transition-color duration-300 text-neutral-200'>
				<label class="font-bold block " for="server_import_dialog_skip_check">{{ __('lychee.UPLOAD_IMPORT_SKIP_DUPLICATES') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_skip_check" wire:model.live='form.skip_duplicates' />
				<p>{{ __('lychee.UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL') }}</p>
			</div>
			<div @class(['relative my-3 pl-9 transition-color duration-300',
				'disabled'=> $form['skip_duplicates'] == false,
				'text-neutral-200' => $form['skip_duplicates'] === true ])>
				<label class="font-bold block " for="server_import_dialog_resync_check">{{ __('lychee.UPLOAD_IMPORT_RESYNC_METADATA') }}</label>
				<x-forms.defaulttickbox id="server_import_dialog_resync_check" wire:model.live='form.resync_metadata' :disabled="$form['skip_duplicates'] === false" />
				<p>{{ __('lychee.UPLOAD_IMPORT_RESYNC_METADATA_EXPL') }}</p>
			</div>
		</form>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-dark-800 rounded-br-md w-full">{{ __('lychee.UPLOAD_IMPORT') }}</x-forms.buttons.action>
	</div>
</div>