
<div id="container" style="padding-bottom: 62px;">
	<!-- Loading -->
	<div id="loading"></div>
	<livewire:components.header :mode="$mode" :title="$this->title" />
	<livewire:components.left-menu>
	@if($mode === App\Enum\PageMode::ALBUMS())
		<livewire:modules.albums/>
	@elseif($mode === App\Enum\PageMode::ALBUM())
		<livewire:modules.album :album="$this->album" />
		<livewire:components.sidebar :album="$this->album"/>
	@elseif($mode === App\Enum\PageMode::PHOTO())
		<livewire:modules.photo :album="$this->album" :photo="$this->photo" />
		<livewire:components.sidebar :album="$this->album" :photo="$this->photo" />
	@elseif($mode === App\Enum\PageMode::MAP())
		<livewire:modules.albums/>
	@endif
	<livewire:components.base.modal />

	{{-- @include('includes.footer') --}}
</div>
