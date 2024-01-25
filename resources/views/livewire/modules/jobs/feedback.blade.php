<div class="fixed bottom-0 right-0">
	@if($display)
	<div x-data="{isOpen: @js($open)}" x-cloak wire:poll>
	@if ($this->job_history->count() > 0)
	<div x-on:click="isOpen = true"
		x-bind:class="isOpen ? 'hidden' : ''"
		class="w-6 h-6 bg-bg-700 cursor-pointer mb-8 mr-8">
		<x-icons.iconic icon="pulse" fill='' class="animate-ping fill-create-600 my-0 w-8 h-8 mr-0 ml-0" />
	</div>
	<div
		x-on:click="isOpen = false"
		x-bind:class="isOpen ? '' : 'hidden'"
		class="bg-bg-700 border-t-2 border-bg-800 border-l-2 border-solid rounded-tl"
		>
		<h2 class="text-center text-text-main-400 font-bold text-sm p-1 px-12">
			<x-icons.iconic icon="pulse" fill='' class="animate-ping fill-create-600 my-0 w-3 h-3 mr-2 ml-0" />
			Jobs Queue (<span class="text-primary-500">{{ $this->num_started }}</span>/<span class="text-ready-400">{{ $this->num_ready }}</span>)</h2>
		<div class="overflow-y-auto h-32 border-t border-t-bg-600 border-solid text-xs p-2">
		@foreach ($this->job_history as $history)
			<span @class([
				'text-ready-400' => $history->status->value === 0,
				'text-danger-700' => $history->status->value === 1,
				'text-create-700' => $history->status->value === 2,
				'text-primary-500' => $history->status->value === 3,
			]) >
			{{ $history->job }} -- {{ $history->updated_at->diffForHumans() }}
			</span><br>
		@endforeach
		</div>
	</div>
	@endif
	</div>
	@endif
</div>