@extends('install.view')

@section('admin_step')
active
@endsection

@section('content')
	@isset($errors)
		<div class="alert alert-warning text-center">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endisset

	@isset($error)
	<p class="alert alert-warning text-center">
		<strong>{!! $error !!}</strong>
	</p>
	@endisset

	<strong>Set up admin account.</strong><br/>
	<form method="post" action="{{ route('install-admin') }}">
		{{-- Create a new user that will be an admin: --}}
		<label for="username">Username</label><input required type="text" name="username" id="username">
		<label for="password">Password</label><input required type="password" name="password" id="password">
		<label for="password">Confirm password</label><input required type="password" name="password_confirmation" id="password_confirmation">

		<div class="buttons-container">
			<button type="submit" class="button float-right" >
				<i class="fa fa-check fa-fw" aria-hidden="true"></i>
				Create admin account
				<i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i>
			</button>
		</div>
	</form>
@endsection