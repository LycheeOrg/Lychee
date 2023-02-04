<div id="lychee_view_container" class="vflex-container">
	<!--
	Content
	Vertically shares space with the footer.
	The minimum height is set such the footer is positioned
	at the bottom even if the content is smaller.
	-->
	{{ $slot }}
	<livewire:components.footer />
</div>
