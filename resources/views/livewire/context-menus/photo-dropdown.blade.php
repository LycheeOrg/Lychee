<div class="w-full relative">
	@if (!$is_starred)
	<x-context-menu.item wire:click='star' icon_class='hover:fill-yello-400' icon='star'>{{ __('lychee.STAR') }}</x-context-menu.item>
	@else
	<x-context-menu.item wire:click='unstar' icon='star'>{{ __('lychee.UNSTAR') }}</x-context-menu.item>
	@endif
	<x-context-menu.item wire:click='tag' icon='tag'>{{ __('lychee.TAG') }}</x-context-menu.item>
	<x-context-menu.item wire:click='setAsCover' icon='folder-cover'>{{ __('lychee.SET_COVER') }}</x-context-menu.item>
	@if($is_header === false)
	<x-context-menu.item wire:click='setAsHeader' icon='image'>{{ __('lychee.SET_HEADER') }}</x-context-menu.item>
	@elseif($is_header)
	<x-context-menu.item wire:click='setAsHeader' icon='x'>{{ __('lychee.REMOVE_HEADER') }}</x-context-menu.item>
	@endif
	<x-context-menu.separator />
	<x-context-menu.item wire:click='rename' icon='pencil'>{{ __('lychee.RENAME') }}</x-context-menu.item>
	<x-context-menu.item wire:click='copyTo' icon='layers'>{{ __('lychee.COPY_TO') }}</x-context-menu.item>
	<x-context-menu.item wire:click='move' icon='transfer'>{{ __('lychee.MOVE') }}</x-context-menu.item>
	<x-context-menu.item wire:click='delete' icon='trash'>{{ __('lychee.DELETE') }}</x-context-menu.item>
	<x-context-menu.item wire:click='download' icon='cloud-download'>{{ __('lychee.DOWNLOAD') }}</x-context-menu.item>
</div>