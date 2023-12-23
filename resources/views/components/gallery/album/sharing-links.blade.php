<div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
    bg-black/80 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
    data-closable="true"
    x-cloak
	{{ $attributes }} >
    <div class="basicModal transition-opacity ease-in duration-1000
        opacity-100 bg-gradient-to-b from-bg-300 to-bg-400
        relative w-[500px] text-sm rounded-md text-text-main-400 animate-moveUp"
        role="dialog" x-on:click.away="sharingLinksOpen = false"
        x-data="qrBuilder()"
        >
        <div class="flex flex-wrap p-9 gap-5 justify-center align-top text-text-main-0/80"
            x-show="!qrCodeOpen"
            >
                <x-icons.iconic class="w-10 h-10 ionicons cursor-pointer" icon="twitter" x-on:click="window.open('{{ $twitter_link }}')" />
                <x-icons.iconic class="w-10 h-10 ionicons cursor-pointer" icon="facebook" x-on:click="window.open('{{ $facebook_link }}')" />
                <x-icons.iconic class="w-10 h-10 cursor-pointer" icon="envelope-closed" x-on:click="window.open('{{ $mailTo_link }}')" />
				<a class="cursor-pointer" x-on:click="
                navigator.clipboard.writeText('{{ $url }}').then(() => $dispatch('notify', [{type:'success', msg:'{{ __("lychee.URL_COPIED_TO_CLIPBOARD") }}'}]));
                " >
    				<x-icons.iconic class="w-10 h-10" icon="link-intact" />
                </a>
				<x-icons.iconic class="w-10 h-10" icon="grid-two-up" x-on:click="qrCodeOpen = true; setQrCode('{{ $rawUrl }}');" />
		</div>
        <div class="flex flex-wrap p-9 gap-5 justify-center align-top text-text-main-0/80" x-show="qrCodeOpen">
            <canvas id="canvas"></canvas>
        </div>
        <div class="basicModal__buttons">
            <x-forms.buttons.cancel
                x-on:click="qrCodeOpen = false; albumFlags.isSharingLinksOpen = false"
                class="border-t border-t-black/20 w-full hover:bg-white/[.02]">
                {{ __('lychee.CLOSE') }}</x-forms.buttons.cancel>
        </div>
    </div>
</div>
