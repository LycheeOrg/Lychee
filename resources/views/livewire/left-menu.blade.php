<div id="lychee_left_menu_container" class="hflex-item-rigid" style="display: none;">
@if($isOpen)
<!-- leftMenu -->
<div class="leftMenu leftMenu__visible">
	<a id="text_settings_close" class="closetxt" data-tabindex="-1" wire:click="close">{{ Lang::get("CLOSE") }}</a>
	<a id="button_settings_close" class="closebtn" data-tabindex="20" wire:click="close">&times;</a>
	<a class="linkMenu" id="button_settings_open" data-tabindex="-1">
		<x-icons.iconic icon="cog"/>{{ Lang::get("SETTINGS") }}
	</a>
	<a class="linkMenu" id="button_users" data-tabindex="-1">
		<x-icons.iconic icon="person" />{{ Lang::get("USERS") }}
	</a>
	<a class="linkMenu" id="button_u2f" data-tabindex="-1">
		<x-icons.iconic icon="key" />{{ Lang::get("U2F") }}
	</a>
	<a class="linkMenu" id="button_sharing" data-tabindex="-1">
		<x-icons.iconic icon="cloud" />{{ Lang::get("SHARING") }}
	</a>
	<a class="linkMenu" id="button_logs" data-tabindex="-1">
		<x-icons.iconic icon="align-left" />{{ Lang::get("LOGS") }}
	</a>
	<a class="linkMenu" id="button_diagnostics" data-tabindex="-1">
		<x-icons.iconic icon="wrench" />{{ Lang::get("DIAGNOSTICS") }}
	</a>
	<a class="linkMenu" id="button_about" data-tabindex="-1">
		<x-icons.iconic icon="info" />{{ Lang::get("ABOUT_LYCHEE") }}
	</a>
	<a class="linkMenu" id="button_signout" data-tabindex="21" wire:click="logout">
		<x-icons.iconic icon="account-logout" />{{ Lang::get("SIGN_OUT") }}
	</a>
</div>
@endif
</div>