@extends('layouts.simple')

@section('head-js')
    <script type="text/javascript" src="dist/landing.js"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="dist/landing.css">
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
            <li>
                <div class="overlay"></div>
                <img src="{{ $infos['background'] }}" alt=""></li>
        </div>
    </div>

    <div id="home_socials" class="animate animate-up">
        @if($infos['facebook'] != '')
            <a href="{{ $infos['facebook'] }}" class="socialicons" id="facebook" target="_blank"></a>
        @endif
        @if($infos['flickr'] != '')
            <a href="{{ $infos['flickr'] }}" class="socialicons" id="flickr" target="_blank"></a>
        @endif
        @if($infos['twitter'] != '')
            <a href="{{ $infos['twitter'] }}" class="socialicons" id="twitter" target="_blank"></a>
        @endif
        @if($infos['instagram'] != '')
            <a href="{{ $infos['instagram'] }}" class="socialicons" id="instagram" target="_blank"></a>
        @endif
        @if($infos['youtube'] != '')
            <a href="{{ $infos['youtube'] }}" class="socialicons" id="youtube" target="_blank"></a>
        @endif
        <div style="clear: both;"></div>
    </div><!-- socials -->



    <div id="footer" class="animate animate-up">
        <p id="home_copyright">All images on this website are subject to Copyright © by {{ $infos['owner'] }} &copy;
            2019</p>
    </div>
@endsection
