<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn " role="dialog">
		<div class="basicModal__content">
			{{ $title }}
			@if($errors->has('wrongLogin'))
				<span syle="color:red; font-weight:bold;">{{ $errors->first('wrongLogin') }}</span>
			@endif
			<form class="force-first-child">
			@foreach($form as $f => $v)
				<div class="input-group stacked">
				@if ($f == "password")
					<input class="text" name="password" autocomplete="current-password" type="password" placeholder="password" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.password">
				@elseif (is_string($f))
					<input class="text" name="{{ $f }}" autocomplete="on" type="text" placeholder="{{ Lang::get($formName['form.'.$f]) }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.{{ $f }}">
				@else
					{{ $f }} not supported yet.
				@endif
				</div>
			@endforeach
			</form>
			<p class="version">
				Lychee <span class="version-number">4.7.0</span>
				<span class="update-status up-to-date-release">
					– <a target="_blank" href="https://github.com/LycheeOrg/Lychee/releases" data-tabindex="-1">Update available!</a>
				</span>
				<span class="update-status up-to-date-git">
					– <a target="_blank" href="https://github.com/LycheeOrg/Lychee" data-tabindex="-1">Update available!</a>
				</span>
			</p>
		</div>
		<div class="basicModal__buttons">
			<a id="basicModal__cancel" class="basicModal__button" data-tabindex="103" wire:click="close">Cancel</a>
			<a id="basicModal__action" class="basicModal__button" data-tabindex="102" wire:click="submit">Sign In</a>
		</div>
	</div>
</div>