<!DOCTYPE HTML>
<html lang="{{ str_replace('_', '-', Lang::get_code()) }}">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title>{{ App\Models\Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE')) }}</title>
		
	<link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}">
	<link rel="apple-touch-icon" href="img/apple-touch-icon-ipad.png" sizes="120x120">
	<link rel="apple-touch-icon" href="img/apple-touch-icon-iphone.png" sizes="152x152">
	<link rel="apple-touch-icon" href="img/apple-touch-icon-iphone-plus.png" sizes="180x180">
	
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=4.0, user-scalable=yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	
	
	<script src="{{ URL::asset('js/app.js') }}"></script>
	
	<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('css/app.css')) }}">
	{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/user.css')) }}"> --}}
	@if (Helpers::getDeviceType()=="television")
	<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/TV.css')) }}">
	@endif

	{{-- @if($rss_enable)
	  @include('feed::links')
	@endif --}}
	
	<script src="{{ URL::asset('dist/Larapass.js') }}"></script>
	
	@yield('head-meta')
@livewireStyles
</head>
<body>
	{{ $slot }}
	@include('includes.svg')
	@livewireScripts
</body>
</html>