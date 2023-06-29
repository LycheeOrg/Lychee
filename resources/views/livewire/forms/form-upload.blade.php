<div>
	<div>
	@foreach ($uploadedThumbs as $uploadedThumb)
		<img src="{{ URL::asset($uploadedThumb) }}" height="50px">
	@endforeach
	</div>
	<div class="basicModal__content" wire:ignore >
		<x-forms.file-pond wire:model='files' multiples />
	</div>
</div>
