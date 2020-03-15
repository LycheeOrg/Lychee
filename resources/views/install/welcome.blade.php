@extends('install.view')

@section('welcome_step')
active
@endsection

@section('content')
<p class="text-center">Welcome to Lychee</p>
<p class="text-center">
  <a href="{{ route('install-req') }}" class="button">Next <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
  </a>
</p>
@endsection