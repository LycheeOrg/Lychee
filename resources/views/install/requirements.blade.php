@extends('install.view')

@section('req_step')
	active
@endsection

@section('content')
	@foreach ( $requirements as $type => $requirement)
	<br>
	<ul class="list">
		<li class="list__item list__title {{ $phpSupportInfo['supported'] ? 'success' : 'error' }}">
		<strong>{{ ucfirst($type) }}</strong>
		@if ($type == 'php')
			<strong><small>(version {{ $phpSupportInfo['minimum'] }} or higher required)</small></strong>
			<span class="float-right">
				<strong>{{ $phpSupportInfo['current'] }}</strong>
				<i class="fa fa-fw fa-{{ $phpSupportInfo['supported'] ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
			</span>
			</li >
		@endif

		@foreach ($requirement as $extention => $enabled)
		<li class="list__item {{ $enabled ? 'success' : 'error' }}" >
			{{ $extention }}
			<i class="fa fa-fw fa-{{ $enabled ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
		</li>
		@endforeach
	</ul >
	@endforeach

	@if (empty($errors) && $phpSupportInfo['supported'])
	<div class="buttons">
		<a class="button" href="{{ route('install-perm') }}">
			Next<i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
		</a>
	</div>
	@else
	<div class="buttons" >
		<a class="button" href="{{ route('install-req') }}">
			<i class="fa fa-refresh" aria-hidden="true"> Re-check</i>
		</a>
	</div>
	@endif
@endsection