<div>
	<div class="basicModal__content">
		<p style="color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;">
			{{ __('lychee.SETTINGS_ADVANCED_SAVE') }}
		</p>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button" wire:click="close">{{ __('lychee.CANCEL') }}</a>
		<a id="basicModal__action" class="basicModal__button red" wire:click="confirm">{{ __('lychee.ENTER') }}</a>
	</div>
</div>