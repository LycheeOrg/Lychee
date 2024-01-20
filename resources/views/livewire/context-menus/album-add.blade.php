<div class="w-full relative">
	<x-context-menu.item wire:click='openUploadModal' icon='image'>{{ __('lychee.UPLOAD_PHOTO') }}</x-context-menu.item>
	<x-context-menu.separator />
	<x-context-menu.item wire:click='openImportFromUrlModal' icon='link-intact'>{{ __('lychee.IMPORT_LINK') }}</x-context-menu.item>
	@can(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [App\Contracts\Models\AbstractAlbum::class])
		@if(Configs::getValueAsString('dropbox_key') !== '')
		<x-context-menu.item wire:click='openImportFromDropboxModal' icon='dropbox' icon_class="ionicons">{{ __('lychee.IMPORT_DROPBOX') }}</x-context-menu.item>
		@endif
	<x-context-menu.item wire:click='openImportFromServerModal' icon='terminal'>{{ __('lychee.IMPORT_SERVER') }}</x-context-menu.item>
	@endcan
	<x-context-menu.separator />
	<x-context-menu.item wire:click='openAlbumCreateModal' icon='folder'>{{ __('lychee.NEW_ALBUM') }}</x-context-menu.item>
	@if($params[Params::PARENT_ID] === null)
	<x-context-menu.item wire:click='openTagAlbumCreateModal' icon='tags'>{{ __('lychee.NEW_TAG_ALBUM') }}</x-context-menu.item>
	@endif
</div>