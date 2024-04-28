<div class="setLogin my-10">
    <form>
        <div class="py-5">
            {{ __('lychee.PASSWORD_TITLE') }}
            <x-forms.inputs.password class="w-full mt-2" wire:model="oldPassword" placeholder="{{ __('lychee.PASSWORD_CURRENT') }}"
            :has_error="$errors->has('oldPassword')" />
            <x-forms.error-message field='oldPassword' />
        </div>
        <div>
            {{ __('lychee.PASSWORD_TEXT') }}
            <x-forms.inputs.text class="w-full mt-2" wire:model="username" placeholder="{{ __('lychee.LOGIN_USERNAME') }}" :has_error="$errors->has('username')" />
            <x-forms.error-message field='username' />
            <x-forms.inputs.password class="mt-2" wire:model="password" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}" :has_error="$errors->has('password')" />
            <x-forms.error-message field='password' />
            <x-forms.inputs.password class="mt-2" wire:model="password_confirmation" placeholder="{{ __('lychee.LOGIN_PASSWORD_CONFIRM') }}" :has_error="$errors->has('confirm')" />
            <x-forms.error-message field='password_confirmation' />
        </div>
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
