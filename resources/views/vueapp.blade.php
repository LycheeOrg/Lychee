<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml"
    xmlns:h="http://java.sun.com/jsf/html"
    xmlns:f="http://java.sun.com/jsf/core"
    xmlns:p="http://primefaces.org/ui"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <x-meta />
    {{-- @include('components.meta.index') --}}
    @vite(['resources/js/app.ts','resources/sass/app.scss'])
    {!! "<script>window.assets_url = '" . URL::asset('') . "'; console.log('" . URL::asset('') . "')</script>" !!}
</head>
{{-- <body class="antialiased"> --}}
<body class="antialiased lychee-dark">
        <x-warning-misconfiguration />
    @include('includes.svg')
	<div id="app" class="w-full">
        <app/>
	</div>
</body>
</html>
