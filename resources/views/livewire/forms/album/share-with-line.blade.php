<div class="w-full flex my-1">
	<p class="w-full flex align-middle">
		<span class="h-4 w-56 inline-block mt-2.5">{{ $username }}</span>
		<span class="w-56 inline-block text-center">
		<x-forms.tickbox title="User can access picture in full size"  wire:model.live='grants_full_photo_access' />
		<x-forms.tickbox title="User can download the album/pictures" wire:model.live='grants_download' />
		<x-forms.tickbox title="User can add other pictures to the album" wire:model.live='grants_upload' />
		<x-forms.tickbox title="User can edit the album" wire:model.live='grants_edit' />
		<x-forms.tickbox title="User can delete content from the album" wire:model.live='grants_delete' />
		</span>
		<a class="basicModal__button pt-1 pb-2 w-20 rounded flex-shrink
			cursor-pointer inline-block font-bold text-center transition-colors ease-in-out select-none
			text-red-800 hover:text-white hover:bg-red-800"  wire:click='delete'>{{ __('lychee.DELETE') }}</a>
	</p>
</div>