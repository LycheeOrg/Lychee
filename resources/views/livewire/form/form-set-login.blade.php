<div class="setLogin">
	<form>
		<p>
			{{ Lang::get('PASSWORD_TITLE') }}
			<input wire:model="oldPassword" class="text" type="password" placeholder="{{ Lang::get('PASSWORD_CURRENT') }}">
		</p>
		<p>
			{{ Lang::get('PASSWORD_TEXT') }}
			<input wire:model="username" class="text" type="text" placeholder="{{ Lang::get('LOGIN_USERNAME') }}">
			<input wire:model="password" class="text" type="password" placeholder="{{ Lang::get('LOGIN_PASSWORD') }}">
			<input wire:model="confirm" class="text" type="password" placeholder="{{ Lang::get('LOGIN_PASSWORD_CONFIRM') }}">
		</p>
		<div class="basicModal__buttons">
			<a id="basicModal__action_password_change" class="basicModal__button " wire:click="submit">{{ Lang::get('PASSWORD_CHANGE') }}</a>
			<a id="basicModal__action_token" class="basicModal__button " wire:click="openApiTokenModal">{{ Lang::get('TOKEN_BUTTON') }}</a>
		</div>
	</form>
</div>
