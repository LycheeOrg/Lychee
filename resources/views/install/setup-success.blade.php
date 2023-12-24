@extends('install.view')

@section('admin_step')
active
@endsection

@section('content')
	<strong>Admin account has been created.</strong>
	<div class="buttons">
	<a href="{{ config('app.livewire') !== true ? route('home') : route('livewire-index') }}" class="button" >
			Open Lychee <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
		</a>
	</div>
@endsection