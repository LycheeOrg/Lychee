@extends('layouts.simple')

@section('head-js')
    <script type="text/javascript" src="{{ App\Assets\Helpers::cacheBusting('dist/landing.js') }}"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="{{ App\Assets\Helpers::cacheBusting('dist/page.css') }}">
	<link type="text/css" rel="stylesheet" href="{{ App\Assets\Helpers::cacheBusting('dist/user.css') }}">
@endsection

@section('content')

    <div id="header" class="animate animate-down">
        <div id="logo">
            <a href="{{ URL::to('/') }}"><h1>{{ $infos['title'] }}<span>{{ $infos['subtitle'] }}</span></h1></a>
        </div><!-- logo -->
    </div><!-- header -->

    @include('includes.menu')

    <div id="content">
        @foreach($contents as $content)
            {!! $content->get_content() !!}
        @endforeach
    </div>


    <div id="socials">
        @include('includes.socials')
    </div><!-- socials -->

    @include('includes.footer')
@endsection