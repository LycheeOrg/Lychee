<div class="w-full">
    <!-- toolbar -->
    <x-header.bar>
		<x-backButtonHeader class="{{ $this->isLoginLeft ? 'order-4' : 'order-0' }}" />
		<!-- NOT LOGGED -->
		<x-header.button x-on:click="loginModalOpen = true" icon="account-login" class="{{ $this->isLoginLeft ? 'order-0' : 'order-4' }}" />
        <x-header.title>{{ $title }}</x-header.title>
    </x-header.bar>
    <!-- albums -->
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)] flex flex-col">
		<div class="h-full flex flex-col justify-center">
			<div class="w-full text-center"><x-icons.iconic icon="eye" /></div>
			<p class="w-full text-center text-text-main-400">{{ __('lychee.VIEW_NO_PUBLIC_ALBUMS') }}</p>
		</div>
        <x-footer />
    </div>
	<div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
bg-black/80 z-50 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
		data-closable="true">
		<div class="basicModal transition-opacity ease-in duration-1000
			opacity-100 bg-gradient-to-b from-bg-300 to-bg-400
			relative w-[500px] text-sm rounded-md text-text-main-400 animate-moveUp
			"
			role="dialog">
			<livewire:modals.login />
		</div>
	</div>
    @if($this->can_use_2fa)
    <x-webauthn.login />
    @endif
</div>
