<div class="w-full relative">
	<x-context-menu.item wire:click='star' icon='star'>{{ __('lychee.STAR') }}</x-context-menu.item>
	<x-context-menu.item wire:click='tag' icon='tag'>{{ __('lychee.TAG') }}</x-context-menu.item>
	<x-context-menu.item wire:click='setAsCover' icon='folder-cover'>{{ __('lychee.SET_COVER') }}</x-context-menu.item>
	<x-context-menu.separator />
	<x-context-menu.item wire:click='rename' icon='pencil'>{{ __('lychee.RENAME') }}</x-context-menu.item>
	<x-context-menu.item wire:click='copyTo' icon='layers'>{{ __('lychee.COPY_TO') }}</x-context-menu.item>
	<x-context-menu.item wire:click='move' icon='transfer'>{{ __('lychee.MOVE') }}</x-context-menu.item>
	<x-context-menu.item wire:click='delete' icon='trash'>{{ __('lychee.DELETE') }}</x-context-menu.item>
	<x-context-menu.item wire:click='download' icon='cloud-download'>{{ __('lychee.DOWNLOAD') }}</x-context-menu.item>
</div>