<div class="p-9 text-text-main-200">
	<h1 class="mb-6 text-center text-xl font-bold text-text-main-0">
		Lychee
		<span class="version-number">{{ $version }}</span>
		@if($is_new_release_available)
			<span class="text-sm font-normal up-to-date-release text-center">
				– <a target="_blank"
						class=" border-b-neutral-200 border-b-[1px] border-dashed"
						rel="noopener"
						href="https://github.com/LycheeOrg/Lychee/releases"
						data-tabindex="-1">{{ __('lychee.UPDATE_AVAILABLE') }}</a>
			</span>
		@elseif($is_git_update_available)
			<span class="text-sm font-normal up-to-date-git">
				– <a target="_blank"
						class=" border-b-neutral-200 border-b-[1px] border-dashed"
						rel="noopener"
						href="https://github.com/LycheeOrg/Lychee"
						data-tabindex="-1">{{ __('lychee.UPDATE_AVAILABLE') }}</a>
			</span>
		@endif
	</h1>
	<h2 class="my-6 font-bold text-text-main-0">{{__("lychee.ABOUT_SUBTITLE") }}</h2>
	<p class="about-desc">
		{!! sprintf(__("lychee.ABOUT_DESCRIPTION"), "https://LycheeOrg.github.io") !!}
	</p>
</div>
