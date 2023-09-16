<div class="w-full relative">
	<x-context-menu.item wire:click='starAll' icon='star'>{{ __('lychee.STAR_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='tagAll' icon='tag'>{{ __('lychee.TAG_ALL') }}</x-context-menu.item>
	<x-context-menu.separator />
	<x-context-menu.item wire:click='renameAll' icon='pencil'>{{ __('lychee.RENAME_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='copyAllTo' icon='layers'>{{ __('lychee.COPY_ALL_TO') }}</x-context-menu.item>
	<x-context-menu.item wire:click='moveAll' icon='transfer'>{{ __('lychee.MOVE_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='deleteAll' icon='trash'>{{ __('lychee.DELETE_ALL') }}</x-context-menu.item>
	<x-context-menu.item wire:click='downloadAll' icon='cloud-download'>{{ __('lychee.DOWNLOAD_ALL') }}</x-context-menu.item>
</div>