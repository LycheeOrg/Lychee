<div class="basicModal__content">
	<h1>
		Lychee
		<span class="version-number">{{ $version }}</span>
	</h1>
	@if($is_new_release_available)
		<span class="update-status up-to-date-release">
			– <a target="_blank"
					rel="noopener"
					href="https://github.com/LycheeOrg/Lychee/releases"
					data-tabindex="-1">{{ __('lychee.UPDATE_AVAILABLE') }}</a>
		</span>
	@elseif($is_git_update_available)
		<span class="update-status up-to-date-git">
			– <a target="_blank"
					rel="noopener"
					href="https://github.com/LycheeOrg/Lychee"
					data-tabindex="-1">{{ __('lychee.UPDATE_AVAILABLE') }}</a>
		</span>
	@endif
	<h2>{{__("lychee.ABOUT_SUBTITLE") }}</h2>
	<p class="about-desc">
		{!! sprintf(__("lychee.ABOUT_DESCRIPTION"), "https://LycheeOrg.github.io") !!}
	</p>
</div>
