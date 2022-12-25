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

@if (empty($errors))
	<strong>We did not detect any errors. However if the migration failed, reopen <a href="{{ route('install-welcome') }}">this page</a>.</strong>
	<div class="buttons">
	   <a href="{{ route('install-admin') }}" class="button" >
			Set up admin account <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
		</a>
	</div>
@else
<div class="buttons" >
	<a class="button" href="{{ route('install-migrate') }}">
		<i class="fa fa-refresh" aria-hidden="true" > Re-try</i>
	</a>
</div>
@endif
@endsection