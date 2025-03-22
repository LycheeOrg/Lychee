<base href="{{ $base_url }}" />
<link type="text/css" rel="stylesheet" href="{{ $user_css_url }}">
<script defer type="text/javascript" src="{{ $user_js_url }}"></script>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=4.0, user-scalable=yes">
<meta name="mobile-web-app-status-bar-style" content="black">
<meta name="mobile-web-app-capable" content="yes">
<meta name="generator" content="Lychee v6">
<!--General Meta Data -->
<title>{{ $page_title }}</title>
<meta name="description" content="{{ $page_description }}">
<meta name="author" content="{{ $site_owner }}">
<meta name="publisher" content="{{ $site_owner }}">
<!-- Twitter Meta Data -->
<meta name="twitter:title" content="{{ $page_title }}">
<meta name="twitter:description" content="{{ $page_description }}">
<meta name="twitter:image" content="{{ $image_url }}">
<!-- OpenGraph Meta Data (e.g. used by Facebook) -->
<meta property="og:title" content="{{ $page_title }}">
<meta property="og:description" content="{{ $page_description }}">
<meta property="og:image" content="{{ $image_url }}">
<meta property="og:url" content="{{ $page_url }}">

<link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-ipad.png') }}" sizes="120x120">
<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-iphone.png') }}" sizes="152x152">
<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-iphone-plus.png') }}" sizes="180x180">
