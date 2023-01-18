<div class="hflex-item-stretch vflex-container">
	<!-- toolbar -->
	<livewire:components.header key="header-{{$this->albumId}}-{{$this->photoId ?? ''}}" :page_mode="App\Enum\Livewire\PageMode::GALLERY" :gallery_mode="$mode" :title="$this->title" :album="$this->album" />
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
		<div id="lychee_view_container" class="vflex-container">
			<!--
			Content
			Vertically shares space with the footer.
			The minimum height is set such the footer is positioned
			at the bottom even if the content is smaller.
			-->
			@if($mode === App\Enum\Livewire\GalleryMode::ALBUMS)
			<livewire:modules.gallery.albums />
			@elseif($mode === App\Enum\Livewire\GalleryMode::ALBUM)
			<!--
				The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
				Do not that those need to not colide with other components, as a result we use prefix-id-time
				strings to avoid such problems.
			-->
			<livewire:modules.gallery.album key="view-album-{{$this->albumId}}" :album="$this->album" />
			@elseif($mode === App\Enum\Livewire\GalleryMode::MAP)
			<!--
				For now
			-->
			<livewire:modules.gallery.albums/>
			@endif
			<livewire:components.footer />
		</div>
		@if($mode === App\Enum\Livewire\GalleryMode::PHOTO)
		<!--
			The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
			Do not that those need to not colide with other components, as a result we use prefix-id-time
			strings to avoid such problems.
		-->
		<livewire:modules.photo key="view-photo-{{$this->photoId}}" :album="$this->album" :photo="$this->photo" />
		@endif
	</div>
	<!-- SIDE BARS --->
	@if($mode === App\Enum\Livewire\GalleryMode::ALBUM)
	<!--
		The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
		Do not that those need to not colide with other components, as a result we use prefix-id-time
		strings to avoid such problems.
	-->
	<livewire:components.sidebar key="sidebar-album-{{$this->albumId}}" :album="$this->album"/>
	@elseif($mode === App\Enum\Livewire\GalleryMode::PHOTO)
		<!--
			The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
			Do not that those need to not colide with other components, as a result we use prefix-id-time
			strings to avoid such problems.
		-->
		<livewire:components.sidebar key="sidebar-photo-{{$this->photoId}}" :album="$this->album" :photo="$this->photo" />
	@endif
	<livewire:components.base.modal />
</div>
