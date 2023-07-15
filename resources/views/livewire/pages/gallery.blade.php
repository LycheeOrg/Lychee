<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		key="header-{{$this->albumId}}-{{$this->photoId ?? ''}}"
		:page_mode="App\Enum\Livewire\PageMode::GALLERY"
		:gallery_mode="$mode"
		:title="$this->title"
		:smartAlbum="$smartAlbum"
		:baseAlbum="$baseAlbum"
		 />
		@if($mode === App\Enum\Livewire\GalleryMode::ALBUMS)
		<livewire:modules.gallery.albums />
		@elseif($mode === App\Enum\Livewire\GalleryMode::ALBUM)
		<livewire:modules.gallery.album 
			key="view-album-{{$this->albumId}}"
			:smartAlbum="$smartAlbum"
			:baseAlbum="$baseAlbum"
		/>
		{{-- <x-footer /> --}}
		@elseif($mode === App\Enum\Livewire\GalleryMode::MAP)
		{{-- <div id="lychee_map_container" class="overlay-container"></div> --}}
		<livewire:modules.gallery.albums />
		@endif
		@if($mode === App\Enum\Livewire\GalleryMode::PHOTO)
		<livewire:modules.gallery.photo key="view-photo-{{$this->photoId}}" :album="$this->album" :photo="$this->photo" />
		@endif

		{{-- <livewire:modules.gallery.sensitive-warning :album="$this->album" /> --}}
		<!-- Upload TODO: Figure out how this works -->
		<div id="upload" class="hidden">
			<input id="upload_files" type="file" name="fileElem[]" multiple="" accept="image/*,video/*,.mov">
			<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml">
		</div>
		<!-- SIDE BARS --->
		<!--
			The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
			Do not that those need to not colide with other components, as a result we use prefix-id-time
			strings to avoid such problems.
		-->
		{{-- <aside id="lychee_sidebar_container" @class(["hflex-item-rigid", "active" => $isSidebarOpen])>
			@if($mode === App\Enum\Livewire\GalleryMode::ALBUM && $this->baseAlbum !== null)
			<livewire:modules.sidebar.album key="sidebar-album-{{$this->albumId}}" :album="$this->baseAlbum" />
			@elseif ($mode === App\Enum\Livewire\GalleryMode::PHOTO)
			<livewire:modules.sidebar.photo key="sidebar-album-{{$this->photoId}}" :photo="$this->photo" />
			@endif
		</aside> --}}
</div>
