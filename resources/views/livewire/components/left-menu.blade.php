<x-leftbar.leftbar open={{ $isOpen }}>
	<x-leftbar.leftbar-item click="close">
		<x-icons.iconic icon="chevron-left" class="w-6 h-6" />
		<span class="text-lg">{{ __("lychee.CLOSE") }}</span>
	</x-leftbar.leftbar-item>
	@can(SettingsPolicy::CAN_EDIT, [App\Models\Configs::class])
	<x-leftbar.leftbar-item action="{{ PageMode::SETTINGS->value }}">
		<x-icons.iconic icon="cog" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.SETTINGS") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	@can(UserPolicy::CAN_EDIT, [App\Models\User::class])
	<x-leftbar.leftbar-item action="{{ PageMode::PROFILE->value }}">
		<x-icons.iconic icon="person" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.PROFILE") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	@can(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [App\Models\User::class])
	<x-leftbar.leftbar-item action="{{ PageMode::USERS->value }}">
		<x-icons.iconic icon="people" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.USERS") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	@can(UserPolicy::CAN_USE_2FA, [App\Models\User::class])
	<x-leftbar.leftbar-item action="{{ PageMode::PROFILE->value }}">
		<x-icons.iconic icon="key" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.U2F") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	@can(AlbumPolicy::CAN_SHARE_WITH_USERS, [App\Contracts\Models\AbstractAlbum::class, null])
	<x-leftbar.leftbar-item >
		<x-icons.iconic icon="cloud" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.SHARING") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	@can(SettingsPolicy::CAN_SEE_LOGS, [App\Models\Configs::class])
	<x-leftbar.leftbar-item href="{{ route('log-viewer.index') }}">
		<x-icons.iconic icon="align-left" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.LOGS") }}</span>
	</x-leftbar.leftbar-item>
	<x-leftbar.leftbar-item action="{{ PageMode::JOBS->value }}">
		<x-icons.iconic icon="align-left" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.JOBS") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	@can(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [App\Models\Configs::class])
	<x-leftbar.leftbar-item action="{{ PageMode::DIAGNOSTICS->value }}">
		<x-icons.iconic icon="wrench" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.DIAGNOSTICS") }}</span>
	</x-leftbar.leftbar-item>
	@endcan
	<x-leftbar.leftbar-item click="openAboutModal">
		<x-icons.iconic icon="info" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.ABOUT_LYCHEE") }}</span>
	</x-leftbar.leftbar-item>
	<x-leftbar.leftbar-item click="logout">
		<x-icons.iconic icon="account-logout" class="w-6 h-6" />
		<span class="flex-1 ml-3 whitespace-nowrap">{{ __("lychee.SIGN_OUT") }}</span>
	</x-leftbar.leftbar-item>
</x-leftbar.leftbar>