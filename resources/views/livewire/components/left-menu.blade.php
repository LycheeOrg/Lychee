<div id="lychee_left_menu_container" @class(['hflex-item-rigid', 'visible' => $isOpen])>
@if($isOpen)
<!-- lychee_left_menu -->
<div id="lychee_left_menu">
	<a class="linkMenu" wire:click="close" id="button_settings_close" data-tabindex="-1"><x-icons.iconic icon="chevron-left" />{{ Lang::get("CLOSE") }}</a>
	<a class="linkMenu" wire:click="openSettings" id="button_settings_open" data-tabindex="-1"><x-icons.iconic icon="cog" />{{ Lang::get("SETTINGS") }}</a>
	<a class="linkMenu" id="button_users" data-tabindex="-1"><x-icons.iconic icon="person" />{{ Lang::get("USER", 'Profile') }}</a>
	<a class="linkMenu" wire:click="openUsers" id="button_users" data-tabindex="-1"><x-icons.iconic icon="people" />{{ Lang::get("USERS") }}</a>
	<a class="linkMenu" id="button_u2f" data-tabindex="-1"><x-icons.iconic icon="key" />{{ Lang::get("U2F") }}</a>
	<a class="linkMenu" id="button_sharing" data-tabindex="-1"><x-icons.iconic icon="cloud" />{{ Lang::get("SHARING") }}</a>
	<a class="linkMenu" wire:click="openLogs" id="button_logs" data-tabindex="-1"><x-icons.iconic icon="align-left" />{{ Lang::get("LOGS") }}</a>
	<a class="linkMenu" wire:click="openDiagnostics" id="button_diagnostics" data-tabindex="-1"><x-icons.iconic icon="wrench" />{{ Lang::get("DIAGNOSTICS") }}</a>
	<a class="linkMenu" wire:click="openAboutModal" id="button_about" data-tabindex="-1"><x-icons.iconic icon="info" />{{ Lang::get("ABOUT_LYCHEE") }}</a>
	<a class="linkMenu" wire:click="logout" id="button_signout" data-tabindex="21"><x-icons.iconic icon="account-logout" />{{ Lang::get("SIGN_OUT") }}</a></div>
@endif
</div>