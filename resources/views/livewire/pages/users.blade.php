<div class="w-full">
    <x-header.bar>
        <x-header.back />
        <x-header.title>{{ __('lychee.USERS') }}</x-header.title>
    </x-header.bar>
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
        <div class="settings_view max-w-3xl text-neutral-400 text-sm mx-auto">
            <div class="w-full mt-5">
                <p>
                    This pages allows you to manage users.
                <ul class="mt-1">
                    <li class="ml-4 pt-2"><x-icons.iconic class="fill-white w-4 h-4" icon="data-transfer-upload" />
                        : When selected, the user can upload content.</li>
                    <li class="ml-4 pt-2"><x-icons.iconic class="fill-white w-4 h-4" icon="lock-unlocked" />
                        : When selected, the user can modify their profile (username, password).</li>
                </ul>
                </p>

            </div>
            <div class="users_view_line w-full flex mt-5">
                <p class="full w-full flex">
                    <span
                        class="inline-block text font-bold w-full py-2 px-1 mr-2 mt-2.5 text-white">{{ __('lychee.USERNAME') }}</span>
                    <span
                        class="inline-block text font-bold w-full py-2 px-1 mr-2 mt-2.5 text-white">{{ __('lychee.LOGIN_PASSWORD') }}</span>
                    <span class="inline-block text_icon mt-2.5 w-12 mx-2" title="{{ __('lychee.ALLOW_UPLOADS') }}">
                        <x-icons.iconic class="fill-white w-4 h-4" icon="data-transfer-upload" />
                    </span>
                    <span class="inline-block text_icon mt-2.5 w-12 mx-1"
                        title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}">
                        <x-icons.iconic class="fill-white w-4 h-4" icon="lock-unlocked" />
                    </span>
                </p>
                <a class="inline-block invisible w-1/6 pt-3 pb-4 border-t border-t-dark-800">Save</a>
            </div>
            @foreach ($this->users as $user)
                <livewire:modules.users.user-line :user="$user" key="user-{{ $user->id }}" />
            @endforeach

            <div class="users_view_line w-full flex my-5">
                <p class="full w-full flex">
                    <x-forms.inputs.text class="w-full mt-4" wire:model="username" type="text"
                        placeholder="{{ __('lychee.LOGIN_USERNAME') }}" />
                    <x-forms.inputs.important class="w-full mt-4" wire:model="password" type="text"
                        placeholder="{{ __('lychee.LOGIN_PASSWORD') }}" />
                    <x-forms.tickbox class="mt-1" title="{{ __('lychee.ALLOW_UPLOADS') }}" wire:model='may_upload' />
                    <x-forms.tickbox class="mt-1" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}"
                        wire:model='may_edit_own_settings' />
                </p>
                <x-forms.buttons.create class="w-1/6 rounded-r-md"
                    wire:click="create">{{ __('lychee.CREATE') }}</x-forms.buttons.create>
            </div>
        </div>
    </div>
</div>
