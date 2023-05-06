<x-view.content :mode="$mode" :title="__('lychee.SETTINGS')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<pre class="logs_diagnostics_view">
			<livewire:modules.diagnostics.errors />
			<livewire:modules.diagnostics.infos />
			<livewire:modules.diagnostics.space />
			<livewire:modules.diagnostics.configurations />
		</pre>
	</div>
</x-view.content>