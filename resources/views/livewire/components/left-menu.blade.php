<div id="lychee_left_menu_container" @class(['hflex-item-rigid', 'visible' => $isOpen])>
@if($isOpen)
<!-- lychee_left_menu -->
<div id="lychee_left_menu">
	<a class="linkMenu" wire:click="close" data-tabindex="-1"><x-icons.iconic icon="chevron-left" />{{ Lang::get("CLOSE") }}</a>
	<a class="linkMenu" wire:click="openSettings" data-tabindex="-1"><x-icons.iconic icon="cog" />{{ Lang::get("SETTINGS") }}</a>
	<a class="linkMenu" wire:click="openProfile" data-tabindex="-1"><x-icons.iconic icon="person" />{{ Lang::get("USER", 'Profile') }}</a>
	<a class="linkMenu" wire:click="openUsers" data-tabindex="-1"><x-icons.iconic icon="people" />{{ Lang::get("USERS") }}</a>
	<a class="linkMenu" data-tabindex="-1"><x-icons.iconic icon="key" />{{ Lang::get("U2F") }}</a>
	<a class="linkMenu" data-tabindex="-1"><x-icons.iconic icon="cloud" />{{ Lang::get("SHARING") }}</a>
	<a class="linkMenu" wire:click="openLogs" data-tabindex="-1"><x-icons.iconic icon="align-left" />{{ Lang::get("LOGS") }}</a>
	<a class="linkMenu" wire:click="openDiagnostics" data-tabindex="-1"><x-icons.iconic icon="wrench" />{{ Lang::get("DIAGNOSTICS") }}</a>
	<a class="linkMenu" wire:click="openAboutModal" data-tabindex="-1"><x-icons.iconic icon="info" />{{ Lang::get("ABOUT_LYCHEE") }}</a>
	<a class="linkMenu" wire:click="logout" data-tabindex="21"><x-icons.iconic icon="account-logout" />{{ Lang::get("SIGN_OUT") }}</a></div>
@endif
</div>