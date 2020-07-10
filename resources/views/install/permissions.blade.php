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
        @foreach ($permission['permission'] as $perm)
            @include('install.permission-line')
        @endforeach
    @endforeach
    </ul>

    @if (!isset($errors))
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