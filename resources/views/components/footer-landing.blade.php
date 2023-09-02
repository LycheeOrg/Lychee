<div id="footer" class="absolute bg-black z-10 left-0 right-0 bottom-0 text-center py-1 px-0 overflow-clip">
	<div id="home_socials" class="fixed bottom-8 left-0 right-0 text-center z-10 opacity-0 translate-y-[300px] animate-ladningAnimateUp">
		@if($facebook !== '')
		<a href="{{ $facebook }}" class="inline-block hover:scale-150 hover:text-neutral-400 transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons" id="facebook" target="_blank" rel="noopener"></a>
		@endif
		@if($flickr !== '')
		<a href="{{ $flickr }}" class="inline-block hover:scale-150 hover:text-neutral-400 transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons" id="flickr" target="_blank" rel="noopener"></a>
		@endif
		@if($twitter !== '')
		<a href="{{ $twitter }}" class="inline-block hover:scale-150 hover:text-neutral-400 transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons" id="twitter" target="_blank" rel="noopener"></a>
		@endif
		@if($instagram !== '')
		<a href="{{ $instagram }}" class="inline-block hover:scale-150 hover:text-neutral-400 transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons" id="instagram" target="_blank" rel="noopener"></a>
		@endif
		@if($youtube !== '')
		<a href="{{ $youtube }}" class="inline-block hover:scale-150 hover:text-neutral-400 transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons" id="youtube" target="_blank" rel="noopener"></a>
		@endif
	</div>
	@isset($copyright)
		<p class="home_copyright uppercase text-white text-3xs font-normal translate-y-[300px] animate-ladningAnimateUp">{{ $copyright }}</p>
	@endisset
	@isset($personal_text)
		<p class="personal_text text-white text-3xs font-normal translate-y-[300px] animate-ladningAnimateUp">{{ $personal_text }}</p>
	@endisset
</div>
