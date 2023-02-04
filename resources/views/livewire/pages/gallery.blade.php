<div class="hflex-item-stretch vflex-container">
	<!-- toolbar -->
	<livewire:components.header
		key="header-{{$this->albumId}}-{{$this->photoId ?? ''}}"
		:page_mode="App\Enum\Livewire\PageMode::GALLERY"
		:gallery_mode="$mode"
		:title="$this->title"
		:smartAlbum="$smartAlbum"
		:baseAlbum="$baseAlbum"
		:photo="$photo"
		 />
	<!--
		This container vertically shares space with the toolbar.
		It fills the remaining vertical space not taken by the toolbar.
		It contains the right sidebar and the workbench.
	-->
	<div class="vflex-item-stretch hflex-container">
		<!--
			The workbench horizontally share space with the right
			sidebar.
			It fills the remaining horizontal space not taken be the
			sidebar.
		-->
		<div id="lychee_workbench_container" class="hflex-item-stretch">
			<!--
				The view container covers the entire workbench and
				contains the content and the footer.
				It provides a vertical scroll bar if the content
				grows too large.
				Opposed to the map view and image view the view container
				holds views which are scrollable (e.g. settings,
				album listings, etc.)
			-->
			@if($mode === App\Enum\Livewire\GalleryMode::ALBUMS)
			<livewire:modules.gallery.albums />
			@elseif($mode === App\Enum\Livewire\GalleryMode::ALBUM)
			<!--
				The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
				Do not that those need to not colide with other components, as a result we use prefix-id-time
				strings to avoid such problems.
			-->
			<livewire:modules.gallery.album
				key="view-album-{{$this->albumId}}"
				:smartAlbum="$smartAlbum"
				:baseAlbum="$baseAlbum"
			/>
			@elseif($mode === App\Enum\Livewire\GalleryMode::MAP)
			<!--
				For now
			-->
			{{-- <div id="lychee_map_container" class="overlay-container"></div> --}}
			<livewire:modules.gallery.albums/>
			@endif
			@if($mode === App\Enum\Livewire\GalleryMode::PHOTO)
			<!--
				The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
				Do not that those need to not colide with other components, as a result we use prefix-id-time
				strings to avoid such problems.
			-->
			<livewire:modules.gallery.photo key="view-photo-{{$this->photoId}}" :album="$this->album" :photo="$this->photo" />
			@endif
			<!-- NSFW Warning -->
			<livewire:modules.gallery.sensitive-warning :album="$this->album" />
			<!-- Upload TODO: Figure out how this works -->
			<div id="upload">
				<input id="upload_files" type="file" name="fileElem[]" multiple="" accept="image/*,video/*,.mov">
				<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml">
			</div>
		</div>
		<!-- SIDE BARS --->
		<!--
			The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
			Do not that those need to not colide with other components, as a result we use prefix-id-time
			strings to avoid such problems.
		-->
		<div id="lychee_sidebar_container" @class(["hflex-item-rigid", "active" => $isSidebarOpen])>
			@if($mode === App\Enum\Livewire\GalleryMode::ALBUM && $this->baseAlbum !== null)
			<livewire:modules.sidebar.album key="sidebar-album-{{$this->albumId}}" :album="$this->baseAlbum" />
			@elseif ($mode === App\Enum\Livewire\GalleryMode::PHOTO)
			<livewire:modules.sidebar.photo key="sidebar-album-{{$this->photoId}}" :photo="$this->photo" />
			@endif
		</div>
	</div>
</div>
