<div class='flex-shrink-0 w-24 flex justify-end' >
{{-- No selection --}}
@can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, $this->album ?? null])
<x-header.button x-show='selectedPhotos.length === 0 && selectedAlbums.length === 0 ' wire:click='openContextMenu' icon='plus' />
@endcan
@can(App\Policies\AlbumPolicy::CAN_EDIT, [App\Contracts\Models\AbstractAlbum::class, $this->album ?? null])
{{-- Albums selection --}}
<x-header.button x-cloak x-show='selectedAlbums.length > 0' x-on:click='renameAlbums' icon='pencil' />
<x-header.button x-cloak x-show='selectedAlbums.length > 0' x-on:click='moveAlbums' icon='transfer' />
<x-header.button x-cloak x-show='selectedAlbums.length > 0' x-on:click='mergeAlbums' icon='collapse-left' />
<x-header.button x-cloak x-show='selectedAlbums.length > 0' x-on:click='deleteAlbums' icon='trash' />
<x-header.button x-cloak x-show='selectedAlbums.length > 0' x-on:click='downloadAlbums' icon='cloud-download' />
{{-- Photo selection --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0 && areSelectedPhotosAllStarred()'
	x-on:click='unstarPhotos' icon='star' fill="fill-yellow-400 hover:fill-white"/>{{-- star --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0 && !areSelectedPhotosAllStarred()'
	x-on:click='starPhotos' icon='star' fill='fill-neutral-400 hover:fill-yellow-400'/>{{-- star --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0' x-on:click='tagPhotos' icon='tag'/>{{-- tag --}}
@if($this->albumId !== null)
<x-header.button x-cloak x-show='selectedPhotos.length === 1' x-on:click='setCover' icon='folder-cover'/>{{-- setAsCover --}}
@endif
<x-header.button x-cloak x-show='selectedPhotos.length > 0' x-on:click='renamePhotos' icon='pencil'/>{{-- rename --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0' x-on:click='copyPhotosTo' icon='layers'/>{{-- copyTo --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0' x-on:click='movePhotos' icon='transfer'/>{{-- move --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0' x-on:click='deletePhotos' icon='trash'/>{{-- delete --}}
<x-header.button x-cloak x-show='selectedPhotos.length > 0' x-on:click='downloadPhotos' icon='cloud-download'/>{{-- download --}}
@endcan
</div>