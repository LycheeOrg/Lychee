<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn login" role="dialog">
		<div class="basicModal__content">
			@if($errors->has('wrongLogin'))
				<span syle="color:red; font-weight:bold;">{{ $errors->first('wrongLogin') }}</span>
			@endif
			<form class="force-first-child">
				<div class="input-group stacked">
					<input class="text" name="username" autocomplete="on" type="text" placeholder="{{ Lang::get('USERNAME') }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.username">
				</div>
				<div class="input-group stacked">
					<input class="text" name="password" autocomplete="current-password" type="password" placeholder="{{ Lang::get('PASSWORD') }}" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.password">
				</div>
			</form>
			<p class="version">
				Lychee
				@if($show_version)
				<span class="version-number">4.7.0</span>
				@endif
				@if($is_new_release_available)
				<span class="update-status up-to-date-release">
					– <a target="_blank" href="https://github.com/LycheeOrg/Lychee/releases" data-tabindex="-1">Update available!</a>
				</span>
				@elseif($is_git_update_available)
				<span class="update-status up-to-date-git">
					– <a target="_blank" href="https://github.com/LycheeOrg/Lychee" data-tabindex="-1">Update available!</a>
				</span>
				@endif
			</p>
		</div>
		<div class="basicModal__buttons">
			<a id="basicModal__cancel" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="close">{{ $cancel }}</a>
			<a id="basicModal__action" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="submit">{{ $validate }}</a>
		</div>
	</div>
</div>