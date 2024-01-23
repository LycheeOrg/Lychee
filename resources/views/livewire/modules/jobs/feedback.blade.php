<div class="fixed bottom-0 right-0" x-data="{isOpen: false}" x-cloak wire:poll>
	@if ($this->job_history->count() > 0)
	<div x-on:click="isOpen = true"
		x-bind:class="isOpen ? 'hidden' : ''"
		class="w-6 h-6 bg-bg-700 cursor-pointer mb-4 mr-4">
		<x-icons.iconic icon="pulse" fill='' class="animate-ping fill-create-600 my-0 w-4 h-4 mr-0 ml-0" />
	</div>
	<div
		x-on:click="isOpen = false"
		x-bind:class="isOpen ? '' : 'hidden'"
		class="bg-bg-700 border-t-2  border-bg-800 border-l-2 border-solid rounded-tl"
		>
		<h2 class="text-center text-text-main-400 font-bold text-sm p-1">Jobs Queue</h2>
		<div class="overflow-y-auto h-32 border-t border-t-bg-600 border-solid text-xs p-2">
		@foreach ($this->job_history as $history)
			<span @class([
				'text-ready-400' => $history->status->value === 0,
				'text-danger-700' => $history->status->value === 1,
				'text-create-700' => $history->status->value === 2,
				'text-warning-700' => $history->status->value === 3,
			]) >
			{{ $history->job }} -- {{ $history->updated_at->diffForHumans() }}<br>
			{{ $history->job }} -- {{ $history->updated_at->diffForHumans() }}<br>
			</span>
		@endforeach
		</div>
	</div>
	@endif
</div>