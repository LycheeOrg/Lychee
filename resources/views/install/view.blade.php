<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Lychee Installer</title>
		<link rel="icon" type="image/png" href="{{ url('installer/assets/img/favicon/favicon-16x16.png') }}" sizes="16x16"/>
		<link rel="icon" type="image/png" href="{{ url('installer/assets/img/favicon/favicon-32x32.png') }}" sizes="32x32"/>
		<link rel="icon" type="image/png" href="{{ url('installer/assets/img/favicon/favicon-96x96.png') }}" sizes="96x96"/>
		<link href="{{ url('installer/assets/css/style.css') }}" rel="stylesheet"/>
	</head>
	<body>
		<div class="master">
			<div class="box">
				<div class="header">
					<h1 class="header__title">{{ $title }}</h1>
				</div>
				<ul class="step">
					<li class="step__divider"></li>
					<li class="step__item @yield('admin_step')" title="Set Up admin account">
						@if($step >= 5)
							<a href="{{ route('install-migrate') }}"><i class="step__icon fa fa-user" aria-hidden="true"></i></a>
						@else
							<i class="step__icon fa fa-user" aria-hidden="true"></i>
						@endif
					</li>
					<li class="step__divider"></li>
					<li class="step__item @yield('migrate_step')" title="Apply the database migration">
						@if($step >= 4)
							<a href="{{ route('install-migrate') }}"><i class="step__icon fa fa-server" aria-hidden="true"></i></a>
						@else
							<i class="step__icon fa fa-server" aria-hidden="true"></i>
						@endif
					</li>
					<li class="step__divider"></li>
					<li class="step__item @yield('env_step')" title="Setting the environment">
						@if($step >= 3)
							<a href="{{ route('install-env') }}"><i class="step__icon fa fa-cog" aria-hidden="true"></i></a>
						@else
							<i class="step__icon fa fa-cog" aria-hidden="true"></i>
						@endif
					</li>
					<li class="step__divider"></li>
					<li class="step__item @yield('perm_step')"  title="Checking Permissions">
						@if($step >= 2)
						<a href="{{ route('install-perm') }}"><i class="step__icon fa fa-key" aria-hidden="true"></i></a>
						@else
							<i class="step__icon fa fa-key" aria-hidden="true"></i>
						@endif
					</li>
					<li class="step__divider"></li>
					<li class="step__item @yield('req_step')" title="Checking Requirements">
						@if($step >= 1)
							<a href="{{ route('install-req') }}"><i class="step__icon fa fa-list" aria-hidden="true"></i></a>
						@else
							<i class="step__icon fa fa-list" aria-hidden="true"></i>
						@endif
					</li>
					<li class="step__divider"></li>
					<li class="step__item @yield('welcome_step')"  title="Welcome!">
					<a href="{{ route('install-welcome') }}"><i class="step__icon fa fa-home" aria-hidden="true"></i></a>
					</li>
					<li class="step__divider"></li>
				</ul>
				<div class="main">

				@if (!empty($errors))
				<p class="alert alert-danger text-center">
					<strong>Please fix the errors before going to the next step.</strong>
				</p>
				@endif

				@yield('content')

			</div>
		</div>
	</div>
</body>
</html>
