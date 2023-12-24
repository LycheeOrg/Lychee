<div class='flex-shrink-0 flex justify-end' >
@if($this->flags->is_map_accessible)
<x-header.button href="{{ route('livewire-map', ['albumId' => $this->albumId]) }}" wire:navigate icon="globe" />
@endif
@if($this->flags->is_mod_frame_enabled)
<x-header.button href="{{ route('livewire-frame', ['albumId' => $this->albumId]) }}" wire:navigate icon="monitor" />
@endif
{{-- No selection --}}
@can(App\Policies\AlbumPolicy::CAN_UPLOAD, [App\Contracts\Models\AbstractAlbum::class, $this->album ?? null])
<x-header.button x-show='select.selectedPhotos.length === 0 && select.selectedAlbums.length === 0 ' wire:click='openContextMenu' icon='plus' />
@endcan
@if($this->rights->can_edit)
{{-- Albums selection --}}
<x-header.button x-cloak x-show='select.selectedAlbums.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='renameAlbums' icon='pencil' />
<x-header.button x-cloak x-show='select.selectedAlbums.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='moveAlbums' icon='transfer' />
<x-header.button x-cloak x-show='select.selectedAlbums.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='mergeAlbums' icon='collapse-left' />
<x-header.button x-cloak x-show='select.selectedAlbums.length > 0' fill='fill-danger-800 hover:fill-danger-600' x-on:click='deleteAlbums' icon='trash' />
<x-header.button x-cloak x-show='select.selectedAlbums.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='downloadAlbums' icon='cloud-download' />
{{-- Photo selection --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0 && select.areSelectedPhotosAllStarred()'
	x-on:click='unstarPhotos' icon='star' fill="fill-yellow-400 hover:fill-yellow-200"/>{{-- star --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0 && !select.areSelectedPhotosAllStarred()'
	x-on:click='starPhotos' icon='star' fill='fill-yellow-200 hover:fill-yellow-400'/>{{-- star --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='tagPhotos' icon='tag'/>{{-- tag --}}
@if($this->albumId !== null)
<x-header.button x-cloak x-show='select.selectedPhotos.length === 1' fill='fill-primary-400 hover:fill-primary-200' x-on:click='setCover' icon='folder-cover'/>{{-- setAsCover --}}
@endif
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='renamePhotos' icon='pencil'/>{{-- rename --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='copyPhotosTo' icon='layers'/>{{-- copyTo --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='movePhotos' icon='transfer'/>{{-- move --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0' fill='fill-danger-800 hover:fill-danger-600' x-on:click='deletePhotos' icon='trash'/>{{-- delete --}}
<x-header.button x-cloak x-show='select.selectedPhotos.length > 0' fill='fill-primary-400 hover:fill-primary-200' x-on:click='downloadPhotos' icon='cloud-download'/>{{-- download --}}
@endif
</div>