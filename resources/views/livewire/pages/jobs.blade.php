<div class="w-full">
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" wire:click="back" />
        <x-header.title>{{ __('lychee.JOBS') }}</x-header.title>
    </x-header.bar>
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<div class="settings_view max-w-4xl text-text-main-400 text-sm mx-auto">
			@forelse($this->jobs as $job)
				<span class="mx-2">{{ $job->created_at }}</span>
				<span @class([
						'mx-2',
						'text-ready-400' => $job->status->value === 0,
						'text-danger-700' => $job->status->value === 2,
						'text-create-700' => $job->status->value === 1,
						'text-primary-500' => $job->status->value === 3,
					])><pre class="inline">{{ str_pad($job->status->name(), 7) }}</pre></span>
				<span class="mx-2">{{ $job->owner->name }}</span>
				<span class="mx-2">{{ $job->job }}</span>
				<br>
			@empty
				No Jobs have been executed yet.
			@endforelse
		</div>
	</div>
</div>