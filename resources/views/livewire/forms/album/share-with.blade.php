<div class="text-neutral-200 text-sm p-9 sm:p-4 xl:px-9 max-sm:w-full sm:min-w-[32rem] flex-shrink-0">
	<div>
		@foreach ($form->values as $idx => $perm)
		{{ $perm->user_id }}
		{{ $perm->username }}
		{{ $perm->grants_full_photo_access }}
		{{ $perm->grants_download }}
		{{ $perm->grants_upload }}
		{{ $perm->grants_edit }}
		{{ $perm->grants_delete }}
		@endforeach
	</div>
	<div class="basicModal__content">
		<p>Select the users to share this album with</p>

		<form>
			<div class="input-group compact-inverse"><label for="share_dialog_user_1">admin</label><input
					type="checkbox" id="share_dialog_user_1" name="1"></div>
		</form>
	</div>
	
	<x-forms.buttons.action class="rounded w-full" wire:click='submit' >{{ __('lychee.SAVE') }}</x-forms.buttons.action>
</div>