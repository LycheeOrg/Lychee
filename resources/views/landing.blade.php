<!DOCTYPE HTML>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <title>{{ $title }}</title>

    <meta name="author" content="Tobias Reich">

    <link type="text/css" rel="stylesheet" href="dist/main.css">
    <link type="text/css" rel="stylesheet" href="dist/user.css">


    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="Lychee-front/images/apple-touch-icon-ipad.png" sizes="120x120">
    <link rel="apple-touch-icon" href="Lychee-front/images/apple-touch-icon-iphone.png" sizes="152x152">
    <link rel="apple-touch-icon" href="Lychee-front/images/apple-touch-icon-iphone-plus.png" sizes="180x180">

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">

</head>
<body>

<!-- Loading -->
<div id="loading"></div>

<!-- Header -->
<header class="header">

    <div class="header__toolbar header__toolbar--public">

        <a class="button" id="button_signin" title="{{ $locale['SIGN_IN'] }}">
            <svg class="iconic"><use xlink:href="#account-login"></use></svg>
        </a>

        <a class="header__title"></a>

        <a class="header__hostedwith">Hosted with Lychee</a>

    </div>
    <div class="header__toolbar header__toolbar--albums">

        <a class="button" id="button_settings" title="{{ $locale['SETTINGS'] }}">
            <svg class="iconic"><use xlink:href="#cog"></use></svg>
        </a>

        <a class="header__title"></a>

        <input class="header__search" type="text" name="search" placeholder="{{ $locale['SEARCH'] }}">
        <a class="header__clear">&times;</a>
        <a class="header__divider"></a>
        <a class="button button_add" title="{{ $locale['ADD'] }}">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>

    </div>
    <div class="header__toolbar header__toolbar--album">

        <a class="button" id="button_back_home" title="{{ $locale['CLOSE_ALBUM'] }}">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title"></a>

        <a class="button button--eye" id="button_share_album" title="{{ $locale['SHARE_ALBUM'] }}">
            <svg class="iconic iconic--eye"><use xlink:href="#eye"></use></svg>
        </a>
        <a class="button" id="button_archive" title="{{ $locale['DOWNLOAD_ALBUM'] }}">
            <svg class="iconic"><use xlink:href="#cloud-download"></use></svg>
        </a>
        <a class="button button--info" id="button_info_album" title="{{ $locale['ABOUT_ALBUM'] }}">
            <svg class="iconic"><use xlink:href="#info"></use></svg>
        </a>
        <a class="button" id="button_trash_album" title="{{ $locale['DELETE_ALBUM'] }}">
            <svg class="iconic"><use xlink:href="#trash"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button button_add" title="{{ $locale['ADD'] }}">
            <svg class="iconic"><use xlink:href="#plus"></use></svg>
        </a>

    </div>
    <div class="header__toolbar header__toolbar--photo">

        <a class="button" id="button_back" title="{{ $locale['CLOSE_PHOTO'] }}">
            <svg class="iconic"><use xlink:href="#chevron-left"></use></svg>
        </a>

        <a class="header__title"></a>

        <a class="button button--star" id="button_star" title="{{ $locale['STAR_PHOTO'] }}">
            <svg class="iconic"><use xlink:href="#star"></use></svg>
        </a>
        <a class="button button--eye" id="button_share" title="{{ $locale['SHARE_PHOTO'] }}">
            <svg class="iconic"><use xlink:href="#eye"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button button--info" id="button_info" title="{{ $locale['ABOUT_PHOTO'] }}">
            <svg class="iconic"><use xlink:href="#info"></use></svg>
        </a>
        <a class="button" id="button_move" title="{{ $locale['MOVE'] }}">
            <svg class="iconic"><use xlink:href="#folder"></use></svg>
        </a>
        <a class="button" id="button_trash" title="{{ $locale['DELETE'] }}">
            <svg class="iconic"><use xlink:href="#trash"></use></svg>
        </a>
        <a class="header__divider"></a>
        <a class="button" id="button_more" title="{{ $locale['MORE'] }}">
            <svg class="iconic"><use xlink:href="#ellipses"></use></svg>
        </a>

    </div>

</header>

<!-- leftMenu -->
<div class="leftMenu"></div>

<!-- Content -->
<div class="content"></div>


<!-- JS -->
{{--<script async type="text/javascript" src="dist/main.js"></script>--}}

</body>
</html>
