<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:title="__('lychee.DIAGNOSTICS')" />
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<pre class="logs_diagnostics_view text-neutral-400 text-xs mx-8">
			<livewire:modules.diagnostics.errors />
			<livewire:modules.diagnostics.infos />
			<livewire:modules.diagnostics.space />
			<livewire:modules.diagnostics.configurations />
		</pre>
	</div>
</div>