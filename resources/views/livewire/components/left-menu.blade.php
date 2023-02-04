<div id="lychee_left_menu_container" @class(['hflex-item-rigid', 'visible' => $isOpen])>
@if($isOpen)
<!-- lychee_left_menu -->
<div id="lychee_left_menu">
	<a class="linkMenu" wire:click="close" data-tabindex="-1"><x-icons.iconic icon="chevron-left" />{{ __("lychee.CLOSE") }}</a>
	@can(SettingsPolicy::CAN_EDIT, [App\Models\Configs::class])
		<a class="linkMenu" wire:click="openSettings" data-tabindex="-1"><x-icons.iconic icon="cog" />{{ __("lychee.SETTINGS") }}</a>
	@endcan
	@can(UserPolicy::CAN_EDIT, [App\Models\User::class])
		<a class="linkMenu" wire:click="openProfile" data-tabindex="-1"><x-icons.iconic icon="person" />{{ __("lychee.PROFILE") }}</a>
	@endcan
	@can(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [App\Models\User::class])
		<a class="linkMenu" wire:click="openUsers" data-tabindex="-1"><x-icons.iconic icon="people" />{{ __("lychee.USERS") }}</a>
	@endcan
	@can(UserPolicy::CAN_USE_2FA, [App\Models\User::class])
		<a class="linkMenu" wire:click="openProfile" data-tabindex="-1"><x-icons.iconic icon="key" />{{ __("lychee.U2F") }}</a>
	@endcan
	@can(AlbumPolicy::CAN_SHARE_WITH_USERS, [App\Contracts\Models\AbstractAlbum::class, null])
		<a class="linkMenu" data-tabindex="-1"><x-icons.iconic icon="cloud" />{{ __("lychee.SHARING") }}</a>
	@endcan
	@can(SettingsPolicy::CAN_SEE_LOGS, [App\Models\Configs::class])
		<a class="linkMenu" wire:click="openLogs" data-tabindex="-1"><x-icons.iconic icon="align-left" />{{ __("lychee.LOGS") }}</a>
	@endcan
	@can(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [App\Models\Configs::class])
		<a class="linkMenu" wire:click="openDiagnostics" data-tabindex="-1"><x-icons.iconic icon="wrench" />{{ __("lychee.DIAGNOSTICS") }}</a>
	@endcan
	<a class="linkMenu" wire:click="openAboutModal" data-tabindex="-1"><x-icons.iconic icon="info" />{{ __("lychee.ABOUT_LYCHEE") }}</a>
	<a class="linkMenu" wire:click="logout" data-tabindex="21"><x-icons.iconic icon="account-logout" />{{ __("lychee.SIGN_OUT") }}</a></div>
@endif
</div>