<aside id="default-sidebar"
	@class([
		'z-40 w-0 h-screen transition-width',
		'w-full' => $isOpen,
		'sm:w-[250px]' => $isOpen,
	])
    aria-label="Sidebar">
    <div class="h-full py-4 overflow-y-auto bg-dark-850 light:bg-neutral-50">
        <ul class="space-y-0.5">
            <x-leftbar.leftbar-item click="close" icon="chevron-left">
                {{ __('lychee.CLOSE') }}
            </x-leftbar.leftbar-item>
            @can(SettingsPolicy::CAN_EDIT, [App\Models\Configs::class])
                <x-leftbar.leftbar-item action="{{ PageMode::SETTINGS->value }}" icon="cog">
                    {{ __('lychee.SETTINGS') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(UserPolicy::CAN_EDIT, [App\Models\User::class])
                <x-leftbar.leftbar-item action="{{ PageMode::PROFILE->value }}" icon="person">
                    {{ __('lychee.PROFILE') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [App\Models\User::class])
                <x-leftbar.leftbar-item action="{{ PageMode::USERS->value }}" icon="people">
                    {{ __('lychee.USERS') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(UserPolicy::CAN_USE_2FA, [App\Models\User::class])
                <x-leftbar.leftbar-item action="{{ PageMode::PROFILE->value }}" icon="key">
                    {{ __('lychee.U2F') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(AlbumPolicy::CAN_SHARE_WITH_USERS, [App\Contracts\Models\AbstractAlbum::class, null])
                <x-leftbar.leftbar-item icon="cloud">
                    {{ __('lychee.SHARING') }}</x-leftbar.leftbar-item>
            @endcan
            @can(SettingsPolicy::CAN_SEE_LOGS, [App\Models\Configs::class])
                <x-leftbar.leftbar-item href="{{ route('log-viewer.index') }}" icon="excerpt">
                    {{ __('lychee.LOGS') }}
                </x-leftbar.leftbar-item>
                <x-leftbar.leftbar-item action="{{ PageMode::JOBS->value }}" icon="project">
                    {{ __('lychee.JOBS') }}
                </x-leftbar.leftbar-item>
            @endcan
            @can(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [App\Models\Configs::class])
                <x-leftbar.leftbar-item action="{{ PageMode::DIAGNOSTICS->value }}" icon="wrench">

                    {{ __('lychee.DIAGNOSTICS') }}</x-leftbar.leftbar-item>
            @endcan
            <x-leftbar.leftbar-item click="openAboutModal" icon="info">
                {{ __('lychee.ABOUT_LYCHEE') }}
            </x-leftbar.leftbar-item>
            <x-leftbar.leftbar-item click="logout" icon="account-logout">
                {{ __('lychee.SIGN_OUT') }}
            </x-leftbar.leftbar-item>
        </ul>
    </div>
</aside>
