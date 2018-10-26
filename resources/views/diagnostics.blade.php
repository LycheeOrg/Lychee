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
</pre>