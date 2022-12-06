<!DOCTYPE html>
<html lang="en">
<head>
	<title>Lychee Logs</title>
	<meta charset="utf-8"/>
</head>
<body>
<pre>
@forelse($logs as $log)
    {{ $log->created_at }} -- {{ str_pad($log->type->value, 7) }} -- {{ $log->function }} -- {{ $log->line }} -- {{ $log->text }}
@empty
	Everything looks fine, Lychee has not reported any problems!
@endforelse
</pre>
</body>
</html>
