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

		@vite(['resources/css/app.css','resources/js/app.js'])
    </head>
	<body class="antialiased bg-dark-700 w-full flex flex-row gap-0 relative">
		@include('includes.svg-livewire')
		<x-notifications />
        @persist('left-menu')
		<livewire:components.left-menu>
        @endpersist('left-menu')
        {{ $slot }}
		<livewire:components.base.modal />
		<livewire:components.base.context-menu />
    </body>
</html>
