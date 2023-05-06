<x-view.content :mode="$mode" :title="__('lychee.LOGS')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<div class="clear_logs_update">
			<a wire:click="clearNoise" id="Clean_Noise" class="basicModal__button">Clean Noise</a>
			<a wire:click="clear" id="Clear" class="basicModal__button">Clear</a>
		</div>
		<pre class="logs_diagnostics_view">
		@forelse($this->logs as $log)
{{ $log->created_at }} -- {{ str_pad($log->type->value, 7) }} -- {{ $log->function }} -- {{ $log->line }} -- {{ $log->text }}
		@empty
Everything looks fine, Lychee has not reported any problems!
		@endforelse
		</pre>
	</div>
</x-view.content>