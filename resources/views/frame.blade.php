@extends('layouts.simple')

@section('head-js')
    {{--<meta http-equiv="refresh" content="30">--}}
    <script src="{{ URL::to('dist/stackblur.min.js') }}"></script>
    <script src="{{ URL::to('dist/frame.js') }}"></script>
@endsection

@section('head-css')
<style>

    body {
        padding: 0;
        margin: 0;
        background-color: #000000;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }

    body.loaded {
        opacity: 1;
    }

    #background_canvas {
        width: 100%;
        height: 100%;
        position: absolute;
        z-index: -1;
    }
    #background {
        width: 100%;
        height: 100%;
        position: absolute;
        display: none;
    }
    #noise {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ URL::to('img/noise.png') }}');
        background-repeat: repeat;
        background-position: 44px 44px;
        z-index: -1;
    }

    .image_container {
        margin: auto;
        width: 95vw;
        height: 95vh;
        text-align: center;
        line-height: 100vh;
    }

    .image_container img {
        vertical-align: middle;
        height: 100%;
        width: 100%;
        object-fit: contain;
        filter: drop-shadow(0px 0px 1px rgba(0,0,0,.3))
        drop-shadow(0px 0px 10px rgba(0,0,0,.3));
        display: none;
    }

</style>
@endsection


@section('content')
<img id="background" src="{{ $thumb }}" />
<canvas id="background_canvas"></canvas>
<div id="noise"></div>
<div class="image_container">
    <img id="picture" src="{{ $url }}" />
</div>
@endsection