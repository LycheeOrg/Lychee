<div id="sensitive_warning" class="
	{{ $isBlurred ? 'backdrop-blur-lg' : 'bg-red-950' }}
	{{ $isOpen ? 'flex' : 'hidden' }}
	fixed
	flex-col align-middle justify-center text-center text-text-main-0 top-14 left-0 h-full w-full"
	wire:click="close">
	@if($text === '')
	<div class="w-full flex justify-center">
	<h1 class="text-xl font-bold border-solid border-b-2 border-white mb-3 w-max">{{ __('lychee.NSFW_HEADER') }}</h1></div>
	<p class="text-base">{{ __('lychee.NSFW_EXPLANATION') }}</p>
	<p class="text-base">{{ __('lychee.TAP_CONSENT') }}</p>
	@else
	{!! $text !!}
	@endif
</div>