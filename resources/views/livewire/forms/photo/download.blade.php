<div>
	<x-gallery.photo.download :photo="$this->photo" />
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full"
			@keydown.enter.window="$wire.close()" wire:click="close">
			{{ __('lychee.CANCEL' ) }}
		</x-forms.buttons.cancel>
	</div>
</div>