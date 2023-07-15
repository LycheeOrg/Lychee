<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::JOBS"
		:title="__('lychee.JOBS')" />
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<div class="settings_view max-w-4xl text-neutral-400 text-sm mx-auto">
			@forelse($this->jobs as $job)
				<span class="mx-2">{{ $job->created_at }}</span>
				@if($job->status->name() === 'success')
					<span class="mx-2 text-green-600"><pre class="inline">{{ str_pad($job->status->name(), 7) }}</pre></span>
				@else
					<span class="mx-2 text-red-700"><pre class="inline">{{ str_pad($job->status->name(), 7) }}</pre></span>
				@endif
				<span class="mx-2">{{ $job->owner->name }}</span>
				<span class="mx-2">{{ $job->title ?? __('lychee.UNSORTED') }}</span>
				<span class="mx-2">{{ $job->job }}</span>
				<br>
			@empty
				No Jobs have been executed yet.
			@endforelse
		</div>
	</div>
</div>