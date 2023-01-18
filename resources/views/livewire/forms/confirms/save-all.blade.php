<div>
	<div class="basicModal__content">
		<p style="color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;">
			{{ Lang::get('SETTINGS_ADVANCED_SAVE') }}
		</p>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button" wire:click="close">{{ Lang::get('CANCEL') }}</a>
		<a id="basicModal__action" class="basicModal__button red" wire:click="confirm">{{ Lang::get('ENTER') }}</a>
	</div>
</div>