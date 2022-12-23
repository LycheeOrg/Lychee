@extends('install.view')

@section('env_step')
active
@endsection

@section('content')
<ul class="list">
    <li class="list__item list__item--env list__title error"><span><i class="fa fa-ban" aria-hidden="true"></i> <strong>Lychee does not create the database.</strong></span></li>
    <li class="list__item list__item--env">1 - Manually create your database and then enter the sql details bellow.</li>
    <li class="list__item list__item--env">2 - If you are migrating from the v3, copy your pictures from <br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code class="folder">version3/uploads/</code> to <code class="folder">version4/public/uploads/</code>.</li>
    <li class="list__item list__item--env">3 - Edit the form below to reflect your desired configuration.</li>
</ul>
<strong>For more details of how those values are used, look in the "config" folder.</strong><br/>
    <form method="post" action="{{ route('install-env') }}">
        <textarea class="textarea" name="envConfig">{{ $env }}</textarea>
        <div class="buttons buttons--right">
            <button class="button button--light" type="submit"><i class="fa fa-floppy-o fa-fw" aria-hidden="true"></i>Save</button>
        </div>
    </form>

    @if($exists == true)
		<form method="post" action="{{ route('install-migrate') }}">
			Create a new user that will be an admin:
			<label for="username">Username</label><input required type="text" name="username" id="username">
			<label for="password">Password</label><input required type="password" name="password" id="password">
			<label for="password">Confirm password</label><input required type="password" name="password_confirmation" id="password_confirmation">

			<div class="buttons-container">
				<button type="submit" class="button float-right" >
					<i class="fa fa-check fa-fw" aria-hidden="true"></i>
					Install
					<i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i>
				</button>
			</div>
		</form>
    @endif
@endsection