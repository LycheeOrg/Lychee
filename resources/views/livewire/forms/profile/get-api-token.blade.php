<div class="p-9">
    <form>
        <label for="token-dialog-token">Token</label>
        <input @disabled($isDisabled) class="mx-2 w-2/3 bg-transparent pt-1 pb-0 px-0.5 h-7
            border-b border-b-solid focus:border-b-sky-400 border-b-neutral-800
             disabled:italic disabled:text-center text-neutral-200 disabled:text-neutral-400
            " value="{{ $token }}" @readonly(true) type="text" />

        <div class="inline">
            <a id="button_reset_token" class='cursor-pointer' title='{{ __('lychee.RESET') }}' wire:click='resetToken'>
                <x-icons.iconic class='my-0 ml-1 w-4 h-4 ionicons  hover:fill-sky-500' icon='reload' />
            </a>
            {{-- <a id="button_copy_token" @class(['button' , 'hidden'=>$isDisabled]) title='{{ __('lychee.URL_COPY_TO_CLIPBOARD') }}'>
                <x-icons.iconic class='my-0 ml-1 ionicons' icon='copy' />
            </a> --}}
            <a id="button_disable_token" tile='{{ __('lychee.DISABLE_TOKEN_TOOLTIP') }}'
                wire:click='disableToken' @class(['cursor-pointer' , 'hidden'=>$isDisabled]) >
                <x-icons.iconic class='w-4 h-4 my-0 ml-1 ionicons hover:fill-red-700' icon='ban' />
            </a>
        </div>
    </form>
</div>
