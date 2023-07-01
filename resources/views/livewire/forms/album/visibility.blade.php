<div class="basicModal basicModal--fadeIn" role="dialog">
	<div class="basicModal__content">
		<form>
			<div class="input-group compact-no-indent">
				<label for="pp_dialog_public_check">Public</label>
				<input type="checkbox" class="slider" id="pp_dialog_public_check" name="is_public">
				<p>Anonymous users can access this album, subject to the restrictions below.</p>
			</div>
			<div class="input-group compact-inverse disabled">
				<label for="pp_dialog_full_photo_check">Original</label>
				<input type="checkbox" id="pp_dialog_full_photo_check" name="grants_full_photo_access" disabled="">
				<p>Anonymous users can behold full-resolution photos.</p>
			</div>
			<div class="input-group compact-inverse disabled">
				<label for="pp_dialog_link_check">Hidden</label>
				<input type="checkbox" id="pp_dialog_link_check" name="is_link_required" disabled="">
				<p>Anonymous users need a direct link to access this album.</p>
			</div>
			<div class="input-group compact-inverse disabled">
				<label for="pp_dialog_downloadable_check">Downloadable</label>
				<input type="checkbox" id="pp_dialog_downloadable_check" name="grants_download" disabled="">
				<p>Anonymous users can download this album.</p>
			</div>
			<div class="input-group compact-inverse disabled">
				<label for="pp_dialog_password_check">Password protected</label>
				<input type="checkbox" id="pp_dialog_password_check" name="is_password_required" disabled="">
				<p>Anonymous users need a shared password to access this album.</p>
				<div class="input-group stacked hidden">
					<input class="text" id="pp_dialog_password_input" name="password" type="text"
						placeholder="Password">
				</div>
			</div>
		</form>
		<hr>
		<form>
			<div class="input-group compact-no-indent">
				<label for="pp_dialog_nsfw_check">Sensitive</label>
				<input type="checkbox" class="slider" id="pp_dialog_nsfw_check" name="is_nsfw">
				<p>Album contains sensitive content.</p>
			</div>
		</form>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button">Cancel</a>
		<a id="basicModal__action" class="basicModal__button">Save</a>
	</div>
</div>