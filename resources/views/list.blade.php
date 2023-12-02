<!DOCTYPE html>
<html lang="en">
<head>
	<title>Lychee</title>
	<meta charset="utf-8"/>
</head>
<body>
<pre>
@forelse($lines as $line)
    {{ $line }}
@empty
	No Data found.
@endforelse
</pre>
</body>
</html>
