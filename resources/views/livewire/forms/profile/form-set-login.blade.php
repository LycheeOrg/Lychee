<div class="setLogin">
	<form>
		<p>
			{{ __('lychee.PASSWORD_TITLE') }}
			<input wire:model="oldPassword" class="text" type="password" placeholder="{{ __('lychee.PASSWORD_CURRENT') }}">
			@error('oldPassword')<span style="color:red; font-weight:bold;">{{ $message }}</span> @enderror
		</p>
		<p>
			{{ __('lychee.PASSWORD_TEXT') }}
			<input wire:model="username" class="text" type="text" placeholder="{{ __('lychee.LOGIN_USERNAME') }}">
			@error('username')<span style="color:red; font-weight:bold;">{{ $message }}</span> @enderror
			<input wire:model="password" class="text" type="password" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}">
			@error('password')<span style="color:red; font-weight:bold;">{{ $message }}</span> @enderror
			<input wire:model="confirm" class="text" type="password" placeholder="{{ __('lychee.LOGIN_PASSWORD_CONFIRM') }}">
			@error('confirm')<span style="color:red; font-weight:bold;">{{ $message }}</span> @enderror
		</p>
		<div class="basicModal__buttons">
			<a class="basicModal__button " wire:click="submit" wire:loading.attr="disabled">{{ __('lychee.PASSWORD_CHANGE') }}</a>
			<a class="basicModal__button " wire:click="openApiTokenModal">{{ __('lychee.TOKEN_BUTTON') }}</a>
		</div>
	</form>
</div>
