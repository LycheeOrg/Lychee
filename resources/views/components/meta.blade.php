<base href="{{ $baseUrl }}" />
<link type="text/css" rel="stylesheet" href="{{ $userCssUrl }}">
<script defer type="text/javascript" src="{{ $userJsUrl }}"></script>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=4.0, user-scalable=yes">
<meta name="mobile-web-app-status-bar-style" content="black">
<meta name="mobile-web-app-capable" content="yes">
<meta name="generator" content="Lychee v6">
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

<link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-ipad.png') }}" sizes="120x120">
<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-iphone.png') }}" sizes="152x152">
<link rel="apple-touch-icon" href="{{ URL::asset('img/apple-touch-icon-iphone-plus.png') }}" sizes="180x180">
