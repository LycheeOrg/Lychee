<div class="w-full relative">
	<x-context-menu.item wire:click='renameAll' icon='pencil'>{{ __('lychee.RENAME_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='mergeAll' icon='collapse-left'>{{ __('lychee.MERGE_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='moveAll' icon='transfer'>{{ __('lychee.MOVE_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='deleteAll' icon='trash'>{{ __('lychee.DELETE_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='downloadAll' icon='cloud-download'>{{ __('lychee.DOWNLOAD_ALL') }}</x-context-menu.item>
</div>