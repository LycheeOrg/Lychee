
<div id="lychee_application_container" class="vflex-item-stretch hflex-container" style="padding-bottom: 62px;">
	<!-- leftMenu -->
	<livewire:components.left-menu>
	<!--
		This container horizontally shares space with the left menu.
		It fills the remaining horizontal space not covered by the left menu.
	-->
	<div class="hflex-item-stretch vflex-container">
		<!-- toolbar -->
		<x-header :mode="$mode" :title="$this->title" :album="$this->album"  />
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
		@if($mode === App\Enum\PageMode::ALBUMS)
			<livewire:modules.albums />
		@elseif($mode === App\Enum\PageMode::ALBUM)
		<!--
			The key="..." attribute ensure that we are triggering a refresh of the child component on reload.
			Do not that those need to not colide with other components, as a result we use prefix-id-time
			strings to avoid such problems.
		-->
			<livewire:modules.album key="view-{{$this->albumId}}-{{ now() }}" :album="$this->album" />
			<livewire:components.sidebar key="sidebar-{{$this->albumId}}-{{ now() }}" :album="$this->album"/>
		@elseif($mode === App\Enum\PageMode::PHOTO)
			<livewire:modules.photo key="sidebar-{{$this->photoId}}-{{ now() }}" :album="$this->album" :photo="$this->photo" />
			<livewire:components.sidebar key="sidebar-{{$this->photoId}}-{{ now() }}" :album="$this->album" :photo="$this->photo" />
		@elseif($mode === App\Enum\PageMode::MAP)
			<livewire:modules.albums/>
		@endif
		</div>
		<livewire:components.base.modal />
	</div>
	{{-- @include('includes.footer') --}}
</div>
