<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn " role="dialog">
		<div class="basicModal__content">
			{{ $title }}
			<form>
	<p class="signIn">
		@foreach($form as $f => $v)
		@if ($f == "password")
				<input class="text" name="password" autocomplete="current-password" type="password" placeholder="password" data-tabindex="{{ Helpers::data_index() }}">
			@elseif (is_string($f))
				<input class="text" name="{{ $f }}" autocomplete="on" type="text" placeholder="{{ Lang::get($formName['form.'.$f]) }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}">
			@else
				{{ $f }} not supported yet.
			@endif
		@endforeach
	</p>
	<p class="version">Lychee <span> – <a target="_blank" href="https://github.com/LycheeOrg/Lychee/releases" data-tabindex="-1">Update available!</a><span></span></span></p>
</form>

		</div>
		<div class="basicModal__buttons">
<a id="basicModal__cancel" class="basicModal__button " wire:click="close" >Cancel</a><a id="basicModal__action" class="basicModal__button ">Sign In</a>
		</div>
	</div>
</div>