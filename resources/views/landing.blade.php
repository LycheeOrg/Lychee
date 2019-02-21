@extends('layouts.simple')

@section('head-js')
    <script type="text/javascript" src="dist/landing.js"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="dist/landing.css">
@endsection

@section('content')

<div id="header" class="animate animate-down">
    <div id="logo" >
        <a href="https://thomasheaton.co.uk"><h1>{{ $infos['title'] }}<span>{{ $infos['subtitle'] }}</span></h1></a>
    </div><!-- logo -->
</div><!-- header -->


<div id="menu_wrap" class="animate animate-down">
    <div id="menu">
        <div class="menu-menu-1-container">
        <ul id="menu-menu-1" class="menu">
            {{--<li id="menu-item-176" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-176"><a href="https://thomasheaton.co.uk/contact/">Contact</a></li>--}}
            {{--<li id="menu-item-41" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-41"><a href="https://thomasheaton.co.uk/blog/">Blog</a></li>--}}
            {{--<li id="menu-item-2196" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2196"><a href="https://thomasheaton.co.uk/about/">About</a></li>--}}
            {{--<li id="menu-item-32" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-32"><a href="/portfolio">Portfolio</a></li>--}}
            <li id="menu-item-32" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-32"><a href="/gallery">Gallery</a></li>
        </ul></div>
    </div><!-- menu -->
</div><!-- wrap -->


<div id="intro">
    <div id="intro_content">
        <h1 class="animate_slower pop-in">{{ $infos['title'] }}</h1>
        <h2><span class="animate_slower pop-in">{{ $infos['subtitle'] }}</span></h2>
    </div><!-- content -->
</div><!-- intro -->


<div id="slides" class="animate_slower pop-in-last">
    <div class="slides-container">
        <li><div class="overlay"></div>
            <img src="{{ $infos['background'] }}" alt=""></li>  </div>
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
    <p id="home_copyright">All images on this website are subject to Copyright © by {{ $infos['owner'] }} &copy; 2019</p>
</div>
@endsection
