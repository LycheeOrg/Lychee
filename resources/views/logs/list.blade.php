<pre>
@foreach($logs as $log)
    {{ $log->created_at }} -- {{ $log->type }} -- {{ $log->function }} -- {{ $log->line }} -- {{ $log->text }}
@endforeach
</pre>