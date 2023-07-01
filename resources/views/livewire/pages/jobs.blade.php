<x-view.content :mode="$mode" :title="__('lychee.JOBS')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<pre class="logs_diagnostics_view">
			@forelse($this->jobs as $job)
{{ $job->created_at }} -- {{ str_pad($job->status->name(), 7) }} -- {{ $job->owner->name }} -- {{ $job->job }} -- {{ $job->title ?? __('lychee.UNSORTED') }}
			@empty
No Jobs have been executed yet.
			@endforelse
		</pre>
	</div>
</x-view.content>