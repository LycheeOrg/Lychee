<div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
    bg-black/80 z-5 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
    data-closable="true"
    x-data="loginWebAuthn('{{ __("lychee.U2F_AUTHENTIFICATION_SUCCESS") }}', '{{ __("lychee.ERROR_TEXT") }}')"
    @keydown.window="if (event.key === 'k') { webAuthnOpen = true }"
    @keydown.escape.window="webAuthnOpen = false"
    x-cloak
    x-show="webAuthnOpen">
    <div class="basicModal transition-opacity ease-in duration-1000
        opacity-100 bg-gradient-to-b from-bg-300 to-bg-400
        relative w-[500px] text-sm rounded-md text-text-main-400 animate-moveUp"
        role="dialog" x-on:click.away="webAuthnOpen = !webAuthnOpen">
        <div class="w-full text-text-main-0/80 text-lg font-bold" x-show="isWebAuthnUnavailable()">
			<h1 class="p-3 text-center w-full">{{ __('lychee.U2F_NOT_SECURE') }}</h1>
        </div>
		<div class="flex flex-wrap gap-0.5 p-9 justify-center align-top text-text-main-0/80" x-show="!isWebAuthnUnavailable()">
            <div class="mb-4 mx-0">
				<x-forms.inputs.text class="w-full" autocomplete="on"
					autofocus
					placeholder="{{ __('lychee.USERNAME') }}"
					autocapitalize="off"
                    x-model="username" />
			</div>
        </div>
        <div class="flex w-full box-border">
            <x-forms.buttons.cancel x-on:click="webAuthnOpen = false" class="border-t border-t-black/20 w-full hover:bg-white/[.02]"> {{ __('lychee.CLOSE') }}</x-forms.buttons.cancel>
            <x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full" x-on:click='login()'>{{ __('lychee.U2F') }}</x-forms.buttons.action>
        </div>
    </div>
</div>
