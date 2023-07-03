<!DOCTYPE HTML>
<html lang="{{ app()->currentLocale() }}">
	<head>
		@include('components.meta.meta')

		{{-- <script async defer src="{{ URL::asset(Helpers::cacheBusting('js/app.js')) }}"></script> --}}
		{{-- <script async defer src="{{ URL::asset(Helpers::cacheBusting('js/webauthn.js')) }}"></script> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('css/filepond.css')) }}" /> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/frontend.css')) }}"> --}}
		{{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/user.css')) }}"> --}}

		@vite('resources/css/app.css')
		@livewireStyles
	</head>
	<body class="antialiased bg-dark-700">
		@include('includes.svg-livewire')
		<livewire:components.left-menu>

		{{ $fullpage }}

		<livewire:components.base.modal />
		<livewire:components.base.context-menu />
		@livewireScripts
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function () {
				window.livewire.on('urlChange', (url) => {
					history.pushState(null, null, url);
				});
			});
		</script>
		<script src="//unpkg.com/alpinejs" defer></script>
		{{-- <script defer src="{{ URL::asset(Helpers::cacheBusting('js/alpine.min.js')) }}"></script> --}}
		{{-- <script defer src="{{ URL::asset(Helpers::cacheBusting('js/filepond.js')) }}"></script> --}}
		{{-- <script defer src="{{ URL::asset(Helpers::cacheBusting('js/justified-layout.min.js')) }}"></script> --}}
</body>
</html>