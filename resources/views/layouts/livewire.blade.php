<!DOCTYPE HTML>
<html lang="{{ app()->currentLocale() }}">
	<head>
		@include('components.meta.index')

		{{-- <script async defer src="{{ URL::asset(Helpers::cacheBusting('js/app.js')) }}"></script> --}}
		{{-- <script async defer src="{{ URL::asset(Helpers::cacheBusting('js/webauthn.js')) }}"></script> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('css/filepond.css')) }}" /> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/frontend.css')) }}"> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/user.css')) }}"> --}}

		@vite('resources/css/app.css')
		@vite('resources/js/app.js')
		@livewireStyles(['nonce' => csp_nonce('script')])
	</head>
	<body class="antialiased bg-dark-700 w-full flex flex-row gap-0 relative">
		@include('includes.svg-livewire')
		<x-notifications />
		<livewire:components.left-menu>

		{{ $fullpage }}

		<livewire:components.base.modal />
		<livewire:components.base.context-menu />
		@livewireScripts(['nonce' => csp_nonce('script')])
		<script type="text/javascript" nonce='{{ csp_nonce('script') }}'>
			document.addEventListener('DOMContentLoaded', function () {
				window.livewire.on('urlChange', (url) => {
					history.pushState(null, null, url);
				});
			});
		</script>
		{{-- <script src="//unpkg.com/alpine.min.js" nonce='{{ csp_nonce('script') }}' defer></script> --}}
		<script defer src="{{ URL::asset(Helpers::cacheBusting('js/alpine.min.js')) }}"></script>
		{{-- <script defer src="{{ URL::asset(Helpers::cacheBusting('js/filepond.js')) }}"></script> --}}
</body>
</html>