<!DOCTYPE HTML>
<html lang="{{ app()->currentLocale() }}">
		<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>{{ $title }}</title>
		<link type="text/css" rel="stylesheet" href="{{ Helpers::cacheBusting('dist/landing.css') }}">
		<link type="text/css" rel="stylesheet" href="{{ $user_css_url }}">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="apple-touch-icon" href="img/apple-touch-icon-ipad.png" sizes="120x120">
		<link rel="apple-touch-icon" href="img/apple-touch-icon-iphone.png" sizes="152x152">
		<link rel="apple-touch-icon" href="img/apple-touch-icon-iphone-plus.png" sizes="180x180">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=4.0, user-scalable=yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="apple-mobile-web-app-capable" content="yes">
		@if($rss_enable)
			@include('feed::links')
		@endif
	</head>
	<body>
		<div id="header" class="animate animate-down">
			<div id="logo">
				<a href="{{ URL::to('/') }}"><h1>{{ $infos['title'] }}<span>{{ $infos['subtitle'] }}</span></h1></a>
			</div><!-- logo -->
		</div><!-- header -->
		<div id="menu_wrap" class="animate animate-down">
			<div id="menu">
				<ul class="menu">
					<li class="menu-item">
						<a href="gallery">{{ __('lychee.GALLERY') }}</a>
					</li>
				</ul>
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
				<ul>
					<li>
						<div class="overlay"></div>
						<img src="{{ $infos['background'] }}" alt="">
					</li>
				</ul>
			</div>
		</div>
		@include('includes.footer')
		<script type="text/javascript" src="{{ Helpers::cacheBusting('dist/landing.js') }}"></script>
		<script defer type="text/javascript" src="{{ $user_js_url }}"></script>
	</body>
</html>
