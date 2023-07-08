<div class="w-full relative">
	<x-menu.item wire:click='openUploadModal' icon='image'>{{ __('lychee.UPLOAD_PHOTO') }}</x-menu.item>
	<x-menu.separator />
	<x-menu.item wire:click='openImportFromUrlModal' icon='link-intact'>{{ __('lychee.IMPORT_LINK') }}</x-menu.item>
	@can(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [App\Contracts\Models\AbstractAlbum::class])
		@if(Configs::getValueAsString('dropbox_key') !== '')
		<x-menu.item icon='dropbox' icon_class="ionicons">{{ __('lychee.IMPORT_DROPBOX') }}</x-menu.item>
		@endif
	<x-menu.item wire:click='openImportFromServerModal' icon='terminal'>{{ __('lychee.IMPORT_SERVER') }}</x-menu.item>
	@endcan
	<x-menu.separator />
	<x-menu.item wire:click='openAlbumCreateModal' icon='folder'>{{ __('lychee.NEW_ALBUM') }}</x-menu.item>
	@if($params['parentId'] === null)
	<x-menu.item wire:click='openTagAlbumCreateModal' icon='tags'>{{ __('lychee.NEW_TAG_ALBUM') }}</x-menu.item>
	@endif
</div>