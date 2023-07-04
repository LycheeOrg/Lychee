<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::DIAGNOSTICS"
		:title="__('lychee.DIAGNOSTICS')" />
	<div class="overflow-clip-auto">
		<pre class="logs_diagnostics_view text-neutral-400 text-xs mx-8">
			<livewire:modules.diagnostics.errors />
			<livewire:modules.diagnostics.infos />
			<livewire:modules.diagnostics.space />
			<livewire:modules.diagnostics.configurations />
		</pre>
	</div>
</div>