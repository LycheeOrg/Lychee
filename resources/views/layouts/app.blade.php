<!DOCTYPE HTML>
<html lang="{{ app()->currentLocale() }}">
	<head>
		@include('components.meta.meta')

	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=4.0, user-scalable=yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">


	<script src="{{ URL::asset('Lychee-front/node_modules/lazysizes/lazysizes.min.js') }}"></script>

	<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('css/app.css')) }}">
	{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('$userCssUrl')) }}"> --}}
	@if (Helpers::getDeviceType()=="television")
	<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/TV.css')) }}">
	@endif

	{{-- @if($rss_enable)
	  @include('feed::links')
	@endif --}}

	@yield('head-meta')
</head>
<body>
	@include('includes.svg')
	{{ $slot }}
</body>
</html>