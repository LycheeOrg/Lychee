<!DOCTYPE html>
<html lang="en">
<head>
	<title>Lychee Jobs</title>
	<meta charset="utf-8"/>
</head>
<body>
<pre>
@forelse($jobs as $job)
    {{ $job->created_at }} -- {{ str_pad($job->status->name(), 7) }} -- {{ $job->owner->name }} -- {{ $job->job }}
@empty
	No Jobs have been executed yet.
@endforelse
</pre>
</body>
</html>
