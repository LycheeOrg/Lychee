<div>
	@once
	<link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/landing.css')) }}">
	<script type="text/javascript" src="{{ URL::asset(Helpers::cacheBusting('dist/landing.js')) }}"></script>
	@endonce

	<div id="header" class="animate animate-down">
		<div id="logo">
			<a href="#"><h1>{{ $title }}<span>{{ $subtitle }}</span></h1></a>
		</div>
	</div>

	<div id="menu_wrap" class="animate animate-down">
		<div id="menu">
			<ul class="menu">
				<li class="menu-item">
					<a href="{{ route('livewire_index', ['page' => 'gallery']) }}">GALLERY</a>
				</li>
			</ul>
		</div>
	</div>

	<div id="intro">
		<div id="intro_content">
			<h1 class="animate_slower pop-in">{{ $title }}</h1>
			<h2><span class="animate_slower pop-in">{{ $subtitle }}</span></h2>
		</div>
	</div>

	<div id="slides" class="animate_slower pop-in-last">
		<div class="slides-container">
			<ul>
				<li>
					<div class="overlay"></div>
					<img src="{{ $background }}" alt="">
				</li>
			</ul>
		</div>
	</div>

	{{-- <livewire:components.footer /> --}}
	{{-- @class(['animate animate-up hide_footer toggled']) /> --}}
	<div id="footer" class="animate_slower animate-up pop-in-last toggled">

		<!-- socials -->
			<div id="home_socials" class="animate animate-up toggled">
						<a href="https://www.facebook.com/PhD-dance-366678150817732/" class="socialicons" id="facebook" target="_blank" rel="noopener"></a>
												<a href="https://instagram.com/phd.dance" class="socialicons" id="instagram" target="_blank" rel="noopener"></a>
							<div style="clear: both;"></div>
		</div>

				<p class="home_copyright">
				All images on this website are subject to copyright by Benoît Viguier © 2018-2022</p>



		</div>
	<!-- TODO: footer -->
</div>
