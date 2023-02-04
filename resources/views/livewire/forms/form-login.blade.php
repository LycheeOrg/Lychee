<div>
	<div class="basicModal__content">
		@if($errors->has('wrongLogin'))
			<span style="color:red; font-weight:bold;">{{ $errors->first('wrongLogin') }}</span>
		@endif
		<form class="force-first-child">
			<div class="input-group stacked">
				<input class="text" autocomplete="on" type="text" placeholder="{{ __('lychee.USERNAME') }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.username">
			</div>
			<div class="input-group stacked">
				<input class="text" autocomplete="current-password" type="password" placeholder="{{ __('lychee.PASSWORD') }}" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.password">
			</div>
		</form>
		<p class="version">
			Lychee
			@if($version !== null)
			<span class="version-number">{{ $version }}</span>
			@endif
			@if($is_new_release_available)
			<span class="update-status up-to-date-release">
				– <a target="_blank"
					rel="noopener"
					href="https://github.com/LycheeOrg/Lychee/releases"
					data-tabindex="-1">{{ __('lychee.UPDATE_AVAILABLE') }}</a>
			</span>
			@elseif($is_git_update_available)
			<span class="update-status up-to-date-git">
				– <a target="_blank"
					rel="noopener"
					href="https://github.com/LycheeOrg/Lychee"
					data-tabindex="-1">{{ __('lychee.UPDATE_AVAILABLE') }}</a>
			</span>
			@endif
		</p>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="close">{{ __('lychee.CANCEL') }}</a>
		<a id="basicModal__action" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="submit" wire:loading.attr="disabled">{{ __('lychee.SIGN_IN') }}</a>
	</div>
</div>
