<div class="setLogin my-10">
    <form>
        <p class="py-5">
            {{ __('lychee.PASSWORD_TITLE') }}
            <x-forms.inputs.password class="w-full mt-2" wire:model="oldPassword" placeholder="{{ __('lychee.PASSWORD_CURRENT') }}" />
            {{-- <input wire:model="oldPassword" @class([
                'w-full py-2 px-1 border-b border-b-solid border-b-neutral-800text-red-400 placeholder:text-neutral-500
                hover:border-b-red-700 focus:border-b-red-700 shadow shadow-white/5',
                'text-white bg-transparent' => !$errors->has('oldPassword'),
                'bg-red-700/10 text-red-400' => $errors->has('oldPassword'),
            ]) type="password"
                placeholder="{{ __('lychee.PASSWORD_CURRENT') }}" /> --}}
            <x-forms.error-message field='oldPassword' />
        </p>
        <p>
            {{ __('lychee.PASSWORD_TEXT') }}
            <x-forms.inputs.text class="w-full mt-2" wire:model="username" placeholder="{{ __('lychee.LOGIN_USERNAME') }}" />
            <x-forms.error-message field='username' />
            <x-forms.inputs.password class="mt-2" wire:model="password" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}" />
            <x-forms.error-message field='password' />
            <x-forms.inputs.password class="mt-2" wire:model="confirm" placeholder="{{ __('lychee.LOGIN_PASSWORD_CONFIRM') }}" />
            <x-forms.error-message field='confirm' />
        </p>
        <div class="basicModal__buttons flex">
            <x-forms.buttons.action wire:click="submit" wire:loading.attr="disabled" class="rounded-md w-full">
                {{ __('lychee.PASSWORD_CHANGE') }}
            </x-forms.buttons.action>
            <x-forms.buttons.action wire:click="openApiTokenModal" class="rounded-md w-full">
                {{ __('lychee.TOKEN_BUTTON') }}
            </x-forms.buttons.action>
        </div>
    </form>
</div>
