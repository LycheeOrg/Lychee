@extends('install.view')

@section('migrate_step')
active
@endsection

@section('content')
<pre><code>
@foreach ($lines as $line)
{{ $line }}
@endforeach
</code></pre>

@if (!isset($errors)) 
    <strong>We did not detect any errors. However if the migration failed,
    remove the installed.log file and reopen <a href="{{ route('install-welcome') }}">this page</a>.</strong>
@else
<div class="buttons" >
    <a class="button" href="{{ route('install-migrate') }}">
        <i class="fa fa-refresh" aria-hidden="true" > Re-try</i>
    </a>
</div>
@endif
@endsection