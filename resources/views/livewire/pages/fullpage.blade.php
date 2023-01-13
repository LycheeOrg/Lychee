
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
			<livewire:modules.album :album="$this->album" />
			<livewire:components.sidebar :album="$this->album"/>
		@elseif($mode === App\Enum\PageMode::PHOTO)
			<livewire:modules.photo :album="$this->album" :photo="$this->photo" />
			<livewire:components.sidebar :album="$this->album" :photo="$this->photo" />
		@elseif($mode === App\Enum\PageMode::MAP)
			<livewire:modules.albums/>
		@endif
		</div>
		<livewire:components.base.modal />
	</div>
	{{-- @include('includes.footer') --}}
</div>
