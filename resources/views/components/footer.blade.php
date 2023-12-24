<div id="footer" class="w-full h-full flex flex-col justify-end flex-wrap
	align-bottom self-end text-center py-5 px-0 text-3xs">
	<!--
	Footer
	Vertically shares space with the content.
	The height of the footer is always the natural height
	of its child elements
	-->
	@if($show_socials)
	<div id="home_socials" class="w-full" style="display: none;">
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
		<p class="home_copyright w-full uppercase text-text-main-400 leading-6 font-normal">{{ $copyright }}</p>
	@endisset
	@isset($personal_text)
		<p class="personal_text w-full text-text-main-400 leading-6 font-normal">{{ $personal_text }}</p>
	@endisset
	@isset($hosted_by)
	<p class="hosted_by w-full uppercase text-text-main-400 leading-6 font-normal">
		<a rel="noopener noreferrer" target="_blank" href="https://LycheeOrg.github.io" tabindex="-1"
		class="underline">{{ $hosted_by }}</a>
	</p>
	@endisset
</div>
