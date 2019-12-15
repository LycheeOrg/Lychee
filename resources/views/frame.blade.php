@extends('layouts.simple')

@section('head-js')
    <script src="{{ URL::to('dist/frame.js') }}"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="{{ App\Assets\Helpers::cacheBusting('dist/frame.css') }}">
@endsection


@section('content')
    <img id="background" src=""/>
    <canvas id="background_canvas"></canvas>
    <div id="noise"></div>
    <div class="image_container">
        <img id="picture" src=""/>
    </div>
@endsection