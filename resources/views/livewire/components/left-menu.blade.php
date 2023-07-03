<x-leftbar.leftbar open={{ $isOpen }}>
    <x-leftbar.leftbar-item click="close" icon="chevron-left" >
		<span class="text-lg">{{ __('lychee.CLOSE') }}</span>
    </x-leftbar.leftbar-item>
    @can(SettingsPolicy::CAN_EDIT, [App\Models\Configs::class])
        <x-leftbar.leftbar-item action="{{ PageMode::SETTINGS->value }}" icon="cog" >
			{{ __('lychee.SETTINGS') }}
        </x-leftbar.leftbar-item>
    @endcan
    @can(UserPolicy::CAN_EDIT, [App\Models\User::class])
        <x-leftbar.leftbar-item action="{{ PageMode::PROFILE->value }}" icon="person" >
			{{ __('lychee.PROFILE') }}
        </x-leftbar.leftbar-item>
    @endcan
    @can(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [App\Models\User::class])
        <x-leftbar.leftbar-item action="{{ PageMode::USERS->value }}" icon="people" >
			{{ __('lychee.USERS') }}
        </x-leftbar.leftbar-item>
    @endcan
    @can(UserPolicy::CAN_USE_2FA, [App\Models\User::class])
        <x-leftbar.leftbar-item action="{{ PageMode::PROFILE->value }}" icon="key" >
			{{ __('lychee.U2F') }}
        </x-leftbar.leftbar-item>
    @endcan
    @can(AlbumPolicy::CAN_SHARE_WITH_USERS, [App\Contracts\Models\AbstractAlbum::class, null])
        <x-leftbar.leftbar-item icon="cloud" >
		{{ __('lychee.SHARING') }}</x-leftbar.leftbar-item>
    @endcan
    @can(SettingsPolicy::CAN_SEE_LOGS, [App\Models\Configs::class])
        <x-leftbar.leftbar-item href="{{ route('log-viewer.index') }}" icon="align-left" >
		{{ __('lychee.LOGS') }}
        </x-leftbar.leftbar-item>
        <x-leftbar.leftbar-item action="{{ PageMode::JOBS->value }}" icon="align-left" >
			{{ __('lychee.JOBS') }}
        </x-leftbar.leftbar-item>
    @endcan
    @can(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [App\Models\Configs::class])
        <x-leftbar.leftbar-item action="{{ PageMode::DIAGNOSTICS->value }}" icon="wrench" >
        
			{{ __('lychee.DIAGNOSTICS') }}</x-leftbar.leftbar-item>
    @endcan
    <x-leftbar.leftbar-item click="openAboutModal" icon="info" >
		{{ __('lychee.ABOUT_LYCHEE') }}
    </x-leftbar.leftbar-item>
    <x-leftbar.leftbar-item click="logout" icon="account-logout" >
		{{ __('lychee.SIGN_OUT') }}
	</x-leftbar.leftbar-item>
</x-leftbar.leftbar>