<!DOCTYPE HTML>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="UTF-8">
        <x-meta />
		{{-- @include('components.meta.index') --}}

		{{-- <script async defer src="{{ URL::asset(Helpers::cacheBusting('js/app.js')) }}"></script> --}}
		{{-- <script async defer src="{{ URL::asset(Helpers::cacheBusting('js/webauthn.js')) }}"></script> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('css/filepond.css')) }}" /> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/frontend.css')) }}"> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/user.css')) }}"> --}}

		{{-- @livewireStyles(['nonce' => csp_nonce('script')]) --}}
		{{-- @livewireScripts(['nonce' => csp_nonce('script')]) --}}
		@vite(['resources/css/app.css','resources/js/app.js'])
    </head>
	<body class="antialiased bg-dark-700 w-full flex flex-row gap-0 relative" x-data="{ leftMenuOpen: false }">
		@include('includes.svg')
		<x-notifications />
        @persist('left-menu')
		<livewire:menus.left-menu>
        @endpersist('left-menu')
        {{ $slot }}
		<livewire:base.context-menu />
		<livewire:base.modal />
		<x-shortcuts />
    </body>
</html>
