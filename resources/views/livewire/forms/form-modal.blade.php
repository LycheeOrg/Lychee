<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn " role="dialog">
		<div class="basicModal__content">
			{{ $title }}
			<form class="force-first-child">
			@foreach($form as $f => $v)
				{{-- Only display the allowed forms --}}
				@if(!in_array($f, $formHidden, true))
					<div class="input-group stacked">
					@if (is_string($f))
						{{-- Work in Progess, will probably evolve --}}
						<input class="text" autocomplete="on" type="text" placeholder="{{ $formLocale[$f] }}" autocapitalize="off" data-tabindex="{{ Helpers::data_index() }}" wire:model="form.{{ $f }}">
						@error('form.' . $f)<span style="color:red; font-weight:bold;">{{ $message }}</span> @enderror
					@else
						{{ $f }} not supported yet.
					@endif
					</div>
				@endif
			@endforeach
			</form>
		</div>
		<div class="basicModal__buttons">
			<a id="basicModal__cancel" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="close">{{ $cancel }}</a>
			<a id="basicModal__action" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="submit">{{ $validate }}</a>
		</div>
	</div>
</div>