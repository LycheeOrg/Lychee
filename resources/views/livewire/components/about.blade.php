<div class="basicModal__content">
	<h1>
		Lychee
		<span class="version-number">{{ $version }}</span>
	</h1>
	@if($is_new_release_available)
		<span class="update-status up-to-date-release">
			– <a target="_blank" href="https://github.com/LycheeOrg/Lychee/releases" data-tabindex="-1">{{ Lang::get('UPDATE_AVAILABLE') }}</a>
		</span>
	@elseif($is_git_update_available)
		<span class="update-status up-to-date-git">
			– <a target="_blank" href="https://github.com/LycheeOrg/Lychee" data-tabindex="-1">{{ Lang::get('UPDATE_AVAILABLE') }}</a>
		</span>
	@endif
	<h2>{{Lang::get("ABOUT_SUBTITLE") }}</h2>
	<p class="about-desc">
		{!! sprintf(Lang::get("ABOUT_DESCRIPTION"), "https://LycheeOrg.github.io") !!}
	</p>
</div>
