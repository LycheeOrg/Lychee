<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn " role="dialog">
		<div class="basicModal__content">
			{{ $title }}
			{{-- @if($errors->has('wrongLogin'))
				<span syle="color:red; font-weight:bold;">{{ $errors->first('wrongLogin') }}</span>
			@endif --}}
			<form class="force-first-child">
			@foreach($form as $f => $v)
				<div class="input-group stacked">
				@if (is_string($f))
					{{-- Work in Progess, will probably evolve --}}
					<input class="text" autocomplete="on" type="text" placeholder="{{ Lang::get($formLocale['form.'.$f]) }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.{{ $f }}">
				@else
					{{ $f }} not supported yet.
				@endif
				</div>
			@endforeach
			</form>
		</div>
		<div class="basicModal__buttons">
			<a id="basicModal__cancel" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="close">{{ $cancel }}</a>
			<a id="basicModal__action" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="submit">{{ $validate }}</a>
		</div>
	</div>
</div>