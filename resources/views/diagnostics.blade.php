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