@extends('layouts.simple')

@section('head-js')
    <script type="text/javascript" src="{{ App\Assets\Helpers::cacheBusting('dist/landing.js') }}"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="{{ App\Assets\Helpers::cacheBusting('dist/landing.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ App\Assets\Helpers::cacheBusting('dist/user.css') }}">
@endsection

@section('content')

    <div id="header" class="animate animate-down">
        <div id="logo">
            <a href="{{ URL::to('/') }}"><h1>{{ $infos['title'] }}<span>{{ $infos['subtitle'] }}</span></h1></a>
        </div><!-- logo -->
    </div><!-- header -->


    @include('includes.menu')

    <div id="intro">
        <div id="intro_content">
            <h1 class="animate_slower pop-in">{{ $infos['title'] }}</h1>
            <h2><span class="animate_slower pop-in">{{ $infos['subtitle'] }}</span></h2>
        </div><!-- content -->
    </div><!-- intro -->


    <div id="slides" class="animate_slower pop-in-last">
        <div class="slides-container">
            <ul>
                <li>
                    <div class="overlay"></div>
                    <img src="{{ $infos['background'] }}" alt="">
                </li>
            </ul>
        </div>
    </div>

    <div id="home_socials" class="animate animate-up">
        @include('includes.socials')
    </div><!-- socials -->

    @include('includes.footer')
@endsection
