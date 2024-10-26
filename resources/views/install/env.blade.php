@extends('install.view')

@section('env_step')
active
@endsection

@section('content')
<ul class="list">
	<li class="list__item list__item--env"><span><i class="fa fa-warning" aria-hidden="true"></i> <strong>Lychee does not create the database.</strong></span></li>
	<li class="list__item list__item--env">1 - Manually create your database and then enter the sql details bellow.</li>
	<li class="list__item list__item--env">2 - If you are migrating from the v3, copy your pictures from <br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code class="folder">version3/uploads/</code> to <code class="folder">version4/public/uploads/</code>.</li>
	<li class="list__item list__item--env">3 - Edit the form below to reflect your desired configuration.</li>
</ul>
<strong>For more details of how those values are used, look in the "config" folder.</strong><br/>
	<form method="post" action="{{ route('install-env') }}">
		<textarea class="textarea" name="envConfig">{{ $env }}</textarea>
		<div class="buttons-container">
			@if($exists == true)
			<a class="button float-right ml-4" href="{{ route('install-migrate') }}">
				<i class="fa fa-check fa-fw" aria-hidden="true"></i> Install
				<i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i>
			</a>
			@endif
			<button class="float-right button button--light" type="submit"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
		</div>	
	</form>
@endsection