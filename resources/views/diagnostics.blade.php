<!DOCTYPE html>
<html lang="en">
<head>
	<title>Lychee Diagnostics</title>
	<meta charset="utf-8"/>
</head>
<body>
<pre>
    Diagnostics
    -----------
@if(count($errors) == 0)
    No critical problems found. Lychee should work without problems!
@else
@foreach($errors as $error)
    {{ $error }}
@endforeach
@endif

    System Information
    ------------------
@foreach($infos as $info)
    {{ $info }}
@endforeach

    Config Information
    ------------------
@foreach($configs as $config)
    {{ $config }}
@endforeach
</pre>
</body>
</html>
