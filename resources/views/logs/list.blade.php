<pre>
@foreach($logs as $log)
    {{ $log->created_at }} -- {{ str_pad($log->type, 7) }} -- {{ $log->function }} -- {{ $log->line }} -- {{ $log->text }}
@endforeach
</pre>