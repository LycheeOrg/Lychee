<!DOCTYPE HTML>
<html lang="{{ app()->currentLocale() }}">
	<head>
		<meta charset="UTF-8">
		<link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}">
		<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-ipad.png') }}" sizes="120x120">
		<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-iphone.png') }}" sizes="152x152">
		<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-iphone-plus.png') }}" sizes="180x180">
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

		<script async defer src="{{ URL::asset(Helpers::cacheBusting('js/app.js')) }}"></script>
		<script async defer src="{{ URL::asset(Helpers::cacheBusting('js/webauthn.js')) }}"></script>
		<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('css/filepond.css')) }}" />
		<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/frontend.css')) }}">
		<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/user.css')) }}">

		@livewireStyles
	</head>
<body class="mode-gallery vflex-container">
	@include('includes.svg')
	<!-- Loading indicator -->
	<div id="loading"></div>
	<!--
		The application container vertically shares space with the loading indicator.
		If fills the remaining vertical space not taken by the loading indicator.
		The application container contains the left menu and the workbench.
		The application container is also that part which is shaded by the
		background of the modal dialog while the loading indicator and (potential)
		error message is not shaded.
	-->
	{{ $fullpage }}
	<!--
		The frame container vertically shares space with the loading indicator.
		If fills the remaining vertical space not taken by the loading indicator.
	-->
	{{ $frame }}
	<livewire:components.base.modal />
	<livewire:components.base.context-menu />
	@livewireScripts
	<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function () {
			window.livewire.on('urlChange', (url) => {
				history.pushState(null, null, url);
			});
		});
	</script>
	<script src="//unpkg.com/alpinejs" defer></script>
	<script defer src="{{ URL::asset(Helpers::cacheBusting('js/alpine.min.js')) }}"></script>
	<script defer src="{{ URL::asset(Helpers::cacheBusting('js/filepond.js')) }}"></script>
</body>
</html>