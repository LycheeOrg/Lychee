<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml"
    xmlns:h="http://java.sun.com/jsf/html"
    xmlns:f="http://java.sun.com/jsf/core"
    xmlns:p="http://primefaces.org/ui"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <x-meta />
    @vite(['resources/js/app.ts','resources/sass/app.css'])
</head>
{{-- Dirty work around but hey, backward compatibility... --}}
@if(Features::active('legacy_v4_redirect'))
<script>
	const hashMatch = document.location.hash.replace("#", "").split("/");
	const albumID = hashMatch[0] ?? '';
	const photoID = hashMatch[1] ?? '';

	if (photoID !== '') {
		window.location = @php
            echo '"'.route('gallery').'/"'
        @endphp + albumID + '/' + photoID;
	} else if (albumID !== '') {
		window.location = @php
            echo '"'.route('gallery').'/"'
        @endphp + albumID;
	}
</script>
@endif
{{-- End of work around... --}}
@if((Configs::get()['dark_mode_enabled'] ?? '1') == '1')
    <body class="antialiased dark">
@else
    <body class="antialiased">
@endif
        <x-warning-misconfiguration />
    @include('includes.svg')
	<div id="app" class="w-full3">
        <app/>
	</div>
</body>
</html>
