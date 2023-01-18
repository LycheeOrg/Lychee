<div>
	<p>
		{!! $description !!}
		<input class="text" wire:model="value" type="text" placeholder="{{ $placeholder }}">
	</p>
	<div class="basicModal__buttons">
		<a wire:click="save" id="basicModal__action_dropbox_change" class="basicModal__button">{{ $action }}</a>
	</div>
</div>
