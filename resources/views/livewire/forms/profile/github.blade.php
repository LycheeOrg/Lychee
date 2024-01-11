<div class="setLogin my-10">
	@if($isEnabled)
	<div class=" text-text-main-100">GitHub token enabled <a wire:click='clear' class='cursor-pointer italic text-text-main-400'>(reset)</a></div>
	@else
	<div class='py-5  text-text-main-200'>
		<a href="{{ $registerRoute }}" class='cursor-pointer'>Setup GitHub</a>
	</div>
	@endif
</div>
