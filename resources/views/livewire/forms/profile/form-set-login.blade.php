<div class="setLogin">
	<form>
		<p>
			{{ __('lychee.PASSWORD_TITLE') }}
			<input wire:model="oldPassword" class="text" type="password" placeholder="{{ __('lychee.PASSWORD_CURRENT') }}">
		</p>
		<p>
			{{ __('lychee.PASSWORD_TEXT') }}
			<input wire:model="username" class="text" type="text" placeholder="{{ __('lychee.LOGIN_USERNAME') }}">
			<input wire:model="password" class="text" type="password" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}">
			<input wire:model="confirm" class="text" type="password" placeholder="{{ __('lychee.LOGIN_PASSWORD_CONFIRM') }}">
		</p>
		<div class="basicModal__buttons">
			<a id="basicModal__action_password_change" class="basicModal__button " wire:click="submit" wire:loading.attr="disabled">{{ __('lychee.PASSWORD_CHANGE') }}</a>
			<a id="basicModal__action_token" class="basicModal__button " wire:click="openApiTokenModal">{{ __('lychee.TOKEN_BUTTON') }}</a>
		</div>
	</form>
</div>
