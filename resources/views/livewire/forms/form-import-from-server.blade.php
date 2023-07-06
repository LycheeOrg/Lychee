<div>
	<div class="basicModal__content">
		<p>{{ __('lychee.UPLOAD_IMPORT_SERVER_INSTR') }}</p>
		<form>
			<div class="input-group stacked">
				<input class='text' id="server_import_dialog_path_input" wire:model='form.paths' type='text' />
			</div>
			<div @class(['input-group compact-inverse', 'disabled'=> $form['import_via_symlink'] == true])>
				<label for="server_import_dialog_delete_imported_check">{{ __('lychee.UPLOAD_IMPORT_DELETE_ORIGINALS') }}</label>
				<input type='checkbox' id="server_import_dialog_delete_imported_check" wire:model='form.delete_imported'
				@disabled($form['import_via_symlink'] == true) />
				<p>{{ __('lychee.UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL') }}</p>
			</div>
			<div @class(['input-group compact-inverse', 'disabled'=> $form['delete_imported'] == true])>
				<label for="server_import_dialog_symlink_check">{{ __('lychee.UPLOAD_IMPORT_VIA_SYMLINK') }}</label>
				<input type='checkbox' id="server_import_dialog_symlink_check" wire:model='form.import_via_symlink'
				@disabled($form['delete_imported'] == true) />
				<p>{{ __('lychee.UPLOAD_IMPORT_VIA_SYMLINK_EXPL') }}</p>
			</div>
			<div class='input-group compact-inverse'>
				<label for="server_import_dialog_skip_check">{{ __('lychee.UPLOAD_IMPORT_SKIP_DUPLICATES') }}</label>
				<input type='checkbox' id="server_import_dialog_skip_check" wire:model='form.skip_duplicates' />
				<p>{{ __('lychee.UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL') }}</p>
			</div>
			<div @class(['input-group compact-inverse','disabled'=> $form['skip_duplicates'] == false])>
				<label for="server_import_dialog_resync_check">{{ __('lychee.UPLOAD_IMPORT_RESYNC_METADATA') }}</label>
				<input type='checkbox' id="server_import_dialog_resync_check" wire:model='form.resync_metadata'
				@disabled($form['skip_duplicates'] == false) />
				<p>{{ __('lychee.UPLOAD_IMPORT_RESYNC_METADATA_EXPL') }}</p>
			</div>
		</form>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button" wire:click="close">{{ __('lychee.CANCEL') }}</a>
		<a id="basicModal__action" class="basicModal__button">{{ __('lychee.UPLOAD_IMPORT') }}</a>
	</div>
</div>