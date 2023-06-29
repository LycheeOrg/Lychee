<div id="{{ $html_id }}" class="{{ $class }}">
	<!--
	Footer
	Vertically shares space with the content.
	The height of the footer is always the natural height
	of its child elements
	-->
	@if($show_socials)
	<div id="home_socials" class="animate animate-up" style="display: none;">
		@if($facebook !== '')
		<a href="{{ $facebook }}" class="socialicons" id="facebook" target="_blank" rel="noopener"></a>
		@endif
		@if($flickr !== '')
		<a href="{{ $flickr }}" class="socialicons" id="flickr" target="_blank" rel="noopener"></a>
		@endif
		@if($twitter !== '')
		<a href="{{ $twitter }}" class="socialicons" id="twitter" target="_blank" rel="noopener"></a>
		@endif
		@if($instagram !== '')
		<a href="{{ $instagram }}" class="socialicons" id="instagram" target="_blank" rel="noopener"></a>
		@endif
		@if($youtube !== '')
		<a href="{{ $youtube }}" class="socialicons" id="youtube" target="_blank" rel="noopener"></a>
		@endif
	</div>
	@endif
	@isset($copyright)
		<p class="home_copyright">{{ $copyright }}</p>
	@endisset
	@isset($personal_text)
		<p class="personal_text">{{ $personal_text }}</p>
	@endisset
	@isset($hosted_by)
	<p class="hosted_by">
		<a rel="noopener noreferrer" target="_blank" href="https://LycheeOrg.github.io" tabindex="-1">{{ $hosted_by }}</a>
	</p>
	@endisset
</div>
