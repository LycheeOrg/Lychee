<div class="hflex-item-stretch vflex-container">
	<!-- toolbar -->
	<livewire:components.header :page_mode="$mode" :title="Lang::get('SETTINGS')" />

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
				<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
					<div class="clear_logs_update">
						<a wire:click="clearNoise" id="Clean_Noise" class="basicModal__button">
							Clean Noise
						</a>
						<a wire:click="clear" id="Clear" class="basicModal__button">
							Clear
						</a>
					</div>
					<pre class="logs_diagnostics_view">
					@forelse($this->logs as $log)
{{ $log->created_at }} -- {{ str_pad($log->type->value, 7) }} -- {{ $log->function }} -- {{ $log->line }} -- {{ $log->text }}
					@empty
Everything looks fine, Lychee has not reported any problems!
					@endforelse
					</pre>
				</div>
				<livewire:components.footer />
			</div>
		</div>
		<livewire:components.base.modal />
	</div>
</div>