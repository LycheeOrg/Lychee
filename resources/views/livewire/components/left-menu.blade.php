<aside id="default-sidebar" x-data="{ leftMenuOpen: @entangle('isOpen') }" x-bind:class="!leftMenuOpen ? '' : 'w-full sm:w-[250px]'"
    class="z-40 w-0 h-screen transition-width duration-500 select-none" aria-label="Sidebar">
    <div class="h-full py-4 overflow-y-auto bg-bg-900">
        <ul class="space-y-0.5">
            <x-leftbar.leftbar-item x-on:click="leftMenuOpen = ! leftMenuOpen" icon="chevron-left">
                {{ __('lychee.CLOSE') }}
            </x-leftbar.leftbar-item>
            @can(SettingsPolicy::CAN_EDIT, [App\Models\Configs::class])
                <x-leftbar.leftbar-item href="{{ route('settings') }}" wire:navigate icon="cog">
                    {{ __('lychee.SETTINGS') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(UserPolicy::CAN_EDIT, [App\Models\User::class])
                <x-leftbar.leftbar-item href="{{ route('profile') }}" wire:navigate icon="person">
                    {{ __('lychee.PROFILE') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [App\Models\User::class])
                <x-leftbar.leftbar-item href="{{ route('users') }}" wire:navigate icon="people">
                    {{ __('lychee.USERS') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(UserPolicy::CAN_EDIT, [App\Models\User::class])
            <x-leftbar.leftbar-item href="{{ route('profile') }}" wire:navigate icon="key">
                {{ __('lychee.U2F') }}
            </x-leftbar.leftbar-item>
            @endcan
            @can(AlbumPolicy::CAN_SHARE_WITH_USERS, [App\Contracts\Models\AbstractAlbum::class, null])
                <x-leftbar.leftbar-item href="{{ route('sharing') }}" wire:navigate icon="cloud">
                    {{ __('lychee.SHARING') }}</x-leftbar.leftbar-item>
            @endcan
            @can(SettingsPolicy::CAN_SEE_LOGS, [App\Models\Configs::class])
                @if(Features::active('log-viewer'))
                <x-leftbar.leftbar-item href="{{ route('log-viewer.index') }}" icon="excerpt">
                    {{ __('lychee.LOGS') }}
                </x-leftbar.leftbar-item>
                @else
                <x-leftbar.disabled-leftbar-item icon="excerpt">
                    {{ __('lychee.LOGS') }}
                </x-leftbar.disabled-leftbar-item>
                @endif
                <x-leftbar.leftbar-item href="{{ route('jobs') }}" wire:navigate icon="project">
                    {{ __('lychee.JOBS') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [App\Models\Configs::class])
                <x-leftbar.leftbar-item href="{{ route('diagnostics') }}" wire:navigate icon="wrench">
                    {{ __('lychee.DIAGNOSTICS') }}
                </x-leftbar.leftbar-item>
            @endcan
            <x-leftbar.leftbar-item wire:click="openAboutModal" icon="info">
                {{ __('lychee.ABOUT_LYCHEE') }}
            </x-leftbar.leftbar-item>
            <x-leftbar.leftbar-item wire:click="logout" icon="account-logout">
                {{ __('lychee.SIGN_OUT') }}
            </x-leftbar.leftbar-item>
            @if ($has_dev_tools)
                <li class="px-4 pt-14 pb-1">
                    <span
                        class="p-2 rounded-lg text-text-main-400 whitespace-nowrap text-base">
                        Dev Tools
                    </span>
                </li>
                @if ($clockwork_url !== null)
                    <x-leftbar.leftbar-item href="{{ $clockwork_url }}" icon="telescope">Clockwork
                        App</x-leftbar.leftbar-item>
                @endif
                @if ($doc_api_url !== null)
                    <x-leftbar.leftbar-item href="{{ $doc_api_url }}" icon="document">Api
                        Documentation</x-leftbar.leftbar-item>
                @endif
            @endif
        </ul>
    </div>
</aside>
