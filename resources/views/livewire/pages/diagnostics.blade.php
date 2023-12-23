<div class="w-full">
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" wire:click="back" />
        <x-header.title>{{ __('lychee.DIAGNOSTICS') }}</x-header.title>
    </x-header.bar>
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
<pre class="logs_diagnostics_view text-text-main-200 text-xs mx-8">
<livewire:modules.diagnostics.errors lazy />
<livewire:modules.diagnostics.infos />
<livewire:modules.diagnostics.space />
<livewire:modules.diagnostics.optimize />
<livewire:modules.diagnostics.configurations />
</pre>
	</div>
</div>