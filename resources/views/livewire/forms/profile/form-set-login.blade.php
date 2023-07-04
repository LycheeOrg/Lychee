<div class="setLogin my-10">
    <form>
        <p class="py-5">
            {{ __('lychee.PASSWORD_TITLE') }}
            <input wire:model="oldPassword" @class([
                'w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent text-red-400 placeholder:text-neutral-500
                hover:border-b-red-700 focus:border-b-red-700 shadow shadow-white/5',
                'bg-red-700/10 text-red-400' => $errors->has('oldPassword'),
            ]) type="password"
                placeholder="{{ __('lychee.PASSWORD_CURRENT') }}" />
            <x-forms.error-message field='oldPassword' />
        </p>
        <p>
            {{ __('lychee.PASSWORD_TEXT') }}
            <input wire:model="username" @class([
                'w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
                hover:border-b-sky-400 focus:border-b-sky-400 shadow shadow-white/5',
                'bg-red-700/10 text-red-400' => $errors->has('username'),
            ]) type="text"
                placeholder="{{ __('lychee.LOGIN_USERNAME') }}" />
            <x-forms.error-message field='username' />
            <input wire:model="password" @class([
                'w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
                hover:border-b-red-700 focus:border-b-red-700 shadow shadow-white/5',
                'bg-red-700/10 text-red-400' => $errors->has('password'),
            ]) type="password"
                placeholder="{{ __('lychee.LOGIN_PASSWORD') }}" />
            <x-forms.error-message field='password' />
            <input wire:model="confirm" @class([
                'w-full py-2 px-1 text-white border-b border-b-solid border-b-neutral-800 bg-transparent placeholder:text-neutral-500
                hover:border-b-red-700 focus:border-b-red-700 shadow shadow-white/5',
                'bg-red-700/10 text-red-400' => $errors->has('password'),
            ]) type="password"
                placeholder="{{ __('lychee.LOGIN_PASSWORD_CONFIRM') }}" />
            <x-forms.error-message field='confirm' />
        </p>
        <div class="basicModal__buttons flex">
            <a class="basicModal__button cursor-pointer transition-colors ease-in-out w-full inline-block text-center pt-3 pb-4
			font-bold text-sky-400 rounded-md hover:text-white hover:bg-sky-500"
                wire:click="submit" wire:loading.attr="disabled">{{ __('lychee.PASSWORD_CHANGE') }}</a>
            <a class="basicModal__button cursor-pointer transition-colors ease-in-out w-full inline-block text-center pt-3 pb-4
			font-bold text-sky-400 rounded-md hover:text-white hover:bg-sky-500"
                wire:click="openApiTokenModal">{{ __('lychee.TOKEN_BUTTON') }}</a>
        </div>
    </form>
</div>
