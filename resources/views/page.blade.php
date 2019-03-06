@extends('layouts.simple')

@section('head-js')
    <script type="text/javascript" src="dist/landing.js"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="dist/page.css">
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

    @include('includes.footer')
@endsection