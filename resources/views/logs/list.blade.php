<pre>
@forelse($logs as $log)
    {{ $log->created_at }} -- {{ str_pad($log->type, 7) }} -- {{ $log->function }} -- {{ $log->line }} -- {{ $log->text }}
@empty
	Everything looks fine, Lychee has not reported any problems!
@endforelse
</pre>