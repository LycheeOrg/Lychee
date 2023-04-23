<div>
	<div class="basicModal__content">
		<x-forms.error-message field='wrongLogin' />
		<form class="force-first-child">
			<div class="input-group stacked">
				<input
				@class(['text', 'error' => $errors->has('form.username')])
				autocomplete="on" type="text" placeholder="{{ __('lychee.USERNAME') }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.username">
			</div>
			<div class="input-group stacked">
				<input
				@class(['text', 'error' => $errors->has('form.password') || $errors->has('wrongLogin')])
				autocomplete="current-password" type="password" placeholder="{{ __('lychee.PASSWORD') }}" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.password">
			</div>
		</form>
		<p class="version">
			Lychee
			@if($version !== null)
			<span class="version-number">{{ $version }}</span>
			@endif
			@if($is_new_release_available)
			<x-messages.update-status href="https://github.com/LycheeOrg/Lychee/releases" />
			@elseif($is_git_update_available)
			<x-messages.update-status href="https://github.com/LycheeOrg/Lychee" />
			@endif
		</p>
	</div>
	<div class="basicModal__buttons">
		<x-forms.button-cancel wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.button-cancel>
		<x-forms.button-action wire:click="submit">{{ __('lychee.SIGN_IN') }}</x-forms.button-action>
	</div>
</div>
