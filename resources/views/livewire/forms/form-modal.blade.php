<div>
	<div class="basicModal__content">
		{{ $title }}
		<form class="force-first-child">
		@foreach($form as $f => $v)
			{{-- Only display the allowed forms --}}
			@if(!in_array($f, $formHidden, true))
				@if (is_string($f))
					<div class="input-group stacked">
						<input
						@class(['text', 'error' => $errors->has('form.' . $f)])
						autocomplete="on"
						type="text"
						placeholder="{{ $formLocale[$f] }}"
						autocapitalize="off"
						data-tabindex="{{ Helpers::data_index() }}"
						wire:model="form.{{ $f }}">
						<x-forms.error-message :field="'form.' . $f" />
					</div>
				@else
					{{ $f }} not supported yet.
				@endif
			@endif
		@endforeach
		</form>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="close">{{ $cancel }}</a>
		<a id="basicModal__action" class="basicModal__button" data-tabindex="{{ Helpers::data_index() }}" wire:click="submit">{{ $validate }}</a>
	</div>
</div>