<div class="basicModal basicModal--fadeIn" role="dialog">
	<div class="basicModal__content">
		<p>Enter a title for the new album:</p>
		<form>
			<div class="input-group stacked">
				<input class="text" name="title" type="text" maxlength="100"
					placeholder="Title"></div>
		</form>
	</div>
	<div class="basicModal__buttons">
		<a id="basicModal__cancel" class="basicModal__button" wire:click="close">{{ $cancel }}</a>
		<a id="basicModal__action" class="basicModal__button" wire:click="submit">{{ $validate }}</a>
	</div>
</div>