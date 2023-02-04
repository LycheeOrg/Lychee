@props(['mode', 'title'])
<div class="hflex-item-stretch vflex-container">
	<!-- toolbar -->
	<livewire:components.header :page_mode="$mode" :title="$title" />

	<!--
		This container vertically shares space with the toolbar.
		It fills the remaining vertical space not taken by the toolbar.
		It contains the right sidebar and the workbench.
	-->
	<div class="vflex-item-stretch hflex-container">
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
		</div>
	</div>
</div>