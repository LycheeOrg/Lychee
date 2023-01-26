<!DOCTYPE HTML>
<<<<<<< HEAD
<html lang="{{ str_replace('_', '-', Lang::get_code()) }}">
	<head>
		<meta charset="UTF-8">
		<link type="text/css" rel="stylesheet" href="dist/frontend.css">
		<link type="text/css" rel="stylesheet" href="dist/user.css">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="apple-touch-icon" href="img/apple-touch-icon-ipad.png" sizes="120x120">
		<link rel="apple-touch-icon" href="img/apple-touch-icon-iphone.png" sizes="152x152">
		<link rel="apple-touch-icon" href="img/apple-touch-icon-iphone-plus.png" sizes="180x180">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=4.0, user-scalable=yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="generator" content="Lychee v4">
		<!--General Meta Data -->
		<title>{{ $pageTitle }}</title>
		<meta name="description" content="{{ $pageDescription }}">
		<meta name="author" content="{{ $siteOwner }}">
		<meta name="publisher" content="{{ $siteOwner }}">
		<!-- Twitter Meta Data -->
		<meta name="twitter:title" content="{{ $pageTitle }}">
		<meta name="twitter:description" content="{{ $pageDescription }}">
		<meta name="twitter:image" content="{{ $imageUrl }}">
		<!-- OpenGraph Meta Data (e.g. used by Facebook) -->
		<meta property="og:title" content="{{ $pageTitle }}">
		<meta property="og:description" content="{{ $pageDescription }}">
		<meta property="og:image" content="{{ $imageUrl }}">
		<meta property="og:url" content="{{ $pageUrl }}">
		<!-- RSS feeds -->
		{{-- @if($rss_enable)
			@include('feed::links')
		@endif --}}
=======
<html lang="{{ app()->currentLocale() }}">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title>{{ App\Models\Configs::getValueAsString('site_title') }}</title>
>>>>>>> lang-support

		<script async defer src="{{ URL::asset('js/app.js') }}"></script>
		<script async defer src="{{ URL::asset('dist/WebAuthn.js') }}"></script>
		@livewireStyles
	</head>
<body>
	@include('includes.svg')
	{{ $slot }}
	@livewireScripts
</body>
</html>