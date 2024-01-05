<!DOCTYPE HTML>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <x-meta />
    {{-- @include('components.meta.index') --}}
    {{-- @livewireStyles(['nonce' => csp_nonce('script')]) --}}
    {{-- @livewireScripts(['nonce' => csp_nonce('script')]) --}}
    @vite(['resources/css/app.css','resources/js/app.ts'])
</head>

<body class="antialiased bg-bg-700 w-full flex flex-row gap-0 relative">
	<x-warning-misconfiguration />
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
