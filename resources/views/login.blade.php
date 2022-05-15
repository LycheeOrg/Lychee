@extends('layouts.simple')

@section('head-js')
    <script type="text/javascript" src="{{ Helpers::cacheBusting('dist/landing.js') }}"></script>
@endsection

@section('head-css')
    <link type="text/css" rel="stylesheet" href="{{ Helpers::cacheBusting('dist/main.css') }}">
@endsection

<style>
.no_content {visibility: hidden;}
</style>

@section('content')

@include('includes.svg')

<header class="header">
    <div class="header__toolbar header__toolbar--public">
        <a class="button" id="button_signin" title="{{ $locale['SIGN_IN'] }}" data-tabindex="{{ Helpers::data_index_r() }}">
          <x-iconic icon='account-login' />
        </a>
    </div>
</header>


<div id="loginbg" class="login">
   <img src="{{ $infos['login_background'] }}" alt="" style="width: 100%;height: 100%;object-fit: cover;">
</div>

<!-- JS -->
<script type="text/javascript" src="{{ Helpers::cacheBusting('dist/main.js') }}"></script>

<script type="text/javascript" >
$(document).ready(function () { lychee.loginDialog(); })
</script>
</div>

@endsection
