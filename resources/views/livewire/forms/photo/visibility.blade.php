<div class="basicModal basicModal--fadeIn" role="dialog"><div class="basicModal__content">
	<form>
		<div class="input-group compact-no-indent">
			<label for="ppp_dialog_public_check">Public</label>
			<input type="checkbox" class="slider" id="ppp_dialog_public_check" name="is_public">
			<p>Anonymous users can view this photo, subject to the restrictions below.</p>
		</div>
		<p id="ppp_dialog_global_expl">The visibility of this photo can be fine-tuned using global Lychee settings. Its current visibility is shown below for informational purposes only.</p>
		<div class="input-group compact-inverse disabled">
			<label for="ppp_dialog_full_photo_check">Original</label>
			<input type="checkbox" id="ppp_dialog_full_photo_check" name="grants_full_photo_access" disabled="disabled">
			<p>Anonymous users can behold full-resolution photo.</p>
		</div>
		<div class="input-group compact-inverse disabled">
			<label for="ppp_dialog_link_check">Hidden</label>
			<input type="checkbox" id="ppp_dialog_link_check" name="is_link_required" disabled="disabled">
			<p>Anonymous users need a direct link to view this photo.</p>
		</div>
		<div class="input-group compact-inverse disabled">
			<label for="ppp_dialog_downloadable_check">Downloadable</label>
			<input type="checkbox" id="ppp_dialog_downloadable_check" name="grants_download" disabled="disabled">
			<p>Anonymous users may download this photo.</p>
		</div>
		<div class="input-group compact-inverse disabled">
			<label for="ppp_dialog_password_check">Password protected</label>
			<input type="checkbox" id="ppp_dialog_password_check" name="is_password_required" disabled="disabled">
			<p>Anonymous users need a shared password to view this photo.</p>
		</div>
	</form></div><div class="basicModal__buttons"><a id="basicModal__cancel" class="basicModal__button">Cancel</a><a id="basicModal__action" class="basicModal__button">Save</a></div>
</div>