@extends('install.view')

@section('perm_step')
	active
@endsection

@section('content')
	@if ($windows)
	<p class="alert alert-warning text-center">
		<strong>You are using Windows, we cannot guarantee that the executable permission are properly set.<br>
			All the <code>executable</code> checks have been overridden with <code>true</code>.
		</strong>
	</p>
	@endif

	<ul class="list">
	@foreach ($permissions as $permission)
		<li class="list__item list__item--permissions">
		<span>{{ $permission['folder'] }}</span>
		@if (count($permission['permission']) < 4)
			@for ($i = 0;$i < 4 - count($permission['permission']); $i++)
				<span class="perm float-right">
					&nbsp;
				</span>
			@endfor
		@endif
		@foreach ($permission['permission'] as $perm)
			<span class="perm float-right">
			@if($perm[1] & 1)
				<i class="fa fa-fw fa-exclamation-circle error"></i>
			@else
				<i class="fa fa-fw fa-check-circle-o success"></i>
			@endif
			{{ $perm[0] }}</span>
		@endforeach
	@endforeach
	</ul>

	@if (empty($errors))
	<div class="buttons">
	   <a href="{{ route('install-env') }}" class="button" >
			Next <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
		</a>
	</div>
	@else
	<div class="buttons">
		<a class="button" href="{{ route('install-perm') }}">
			<i class="fa fa-refresh" aria-hidden="true" > Re-check</i>
		</a>
	</div>
	@endif

@endsection