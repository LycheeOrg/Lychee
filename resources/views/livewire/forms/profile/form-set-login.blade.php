<div class="setLogin">
	<form>
		<p>
			{{ __('lychee.PASSWORD_TITLE') }}
			<input
				wire:model="oldPassword"
				@class(['text', 'error' => $errors->has('oldPassword')])
				type="password"
				placeholder="{{ __('lychee.PASSWORD_CURRENT') }}" />
			<x-forms.error-message field='oldPassword' />
		</p>
		<p>
			{{ __('lychee.PASSWORD_TEXT') }}
			<input
				wire:model="username"
				@class(['text', 'error' => $errors->has('username')])
				type="text"
				placeholder="{{ __('lychee.LOGIN_USERNAME') }}" />
				<x-forms.error-message field='username' />
			<input
				wire:model="password"
				@class(['text', 'error' => $errors->has('password')])
				type="password"
				placeholder="{{ __('lychee.LOGIN_PASSWORD') }}" />
				<x-forms.error-message field='password' />
			<input
				wire:model="confirm"
				@class(['text', 'error' => $errors->has('password')])
				type="password"
				placeholder="{{ __('lychee.LOGIN_PASSWORD_CONFIRM') }}" />
				<x-forms.error-message field='confirm' />
		</p>
		<div class="basicModal__buttons">
			<a class="basicModal__button " wire:click="submit" wire:loading.attr="disabled">{{ __('lychee.PASSWORD_CHANGE') }}</a>
			<a class="basicModal__button " wire:click="openApiTokenModal">{{ __('lychee.TOKEN_BUTTON') }}</a>
		</div>
	</form>
</div>
