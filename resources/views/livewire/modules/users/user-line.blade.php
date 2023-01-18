<div class="users_view_line">
	<p>
		<input class="text" wire:model="username" type="text" value="test" placeholder="Username">
		<input class="text" wire:model="password" type="text" placeholder="new password">
		<span class="choice" title="Allow uploads">
			<label>
				<input wire:model='may_upload' type="checkbox">
				<span class="checkbox">
					<x-icons.iconic icon="check" />
				</span>
			</label>
		</span>
		<span class="choice" title="Allow self-management of user account">
			<label>
				<input wire:model='may_edit_own_settings' type="checkbox">
				<span class="checkbox">
					<x-icons.iconic icon="check" />
				</span>
			</label>
		</span>
	</p>
	@if($this->hasChanged)
	<a wire:click='save' class="basicModal__button basicModal__button_OK basicModal__button_OK_no_DEL">Save</a>
	@elseif($user->may_administrate !== true)
	<a wire:click='delete' class="basicModal__button basicModal__button_DEL basicModal__button_OK_no_DEL">Delete</a>
	@endif
</div>