<!-- leftMenu -->
<div class="leftMenu">
	<a id="text_settings_close" class="closetxt" data-tabindex="-1">{{ Lang::get("CLOSE") }}</a>
	<a id="button_settings_close" class="closebtn" data-tabindex="20">&times;</a>
	<a class="linkMenu" id="button_settings_open" data-tabindex="-1">
		<x-iconic icon="cog"/>{{ Lang::get("SETTINGS") }}
	</a>
	<a class="linkMenu" id="button_users" data-tabindex="-1">
		<x-iconic icon="person" />{{ Lang::get("USERS") }} 
	</a>
	<a class="linkMenu" id="button_u2f" data-tabindex="-1">
		<x-iconic icon="key" />{{ Lang::get("U2F") }} 
	</a>
	<a class="linkMenu" id="button_sharing" data-tabindex="-1">
		<x-iconic icon="cloud" />{{ Lang::get("SHARING") }}
	</a>
	<a class="linkMenu" id="button_logs" data-tabindex="-1">
		<x-iconic icon="align-left" />{{ Lang::get("LOGS") }}
	</a>
	<a class="linkMenu" id="button_diagnostics" data-tabindex="-1">
		<x-iconic icon="wrench" />{{ Lang::get("DIAGNOSTICS") }}
	</a>
	<a class="linkMenu" id="button_about" data-tabindex="-1">
		<x-iconic icon="info" />{{ Lang::get("ABOUT_LYCHEE") }}
	</a>
	<a class="linkMenu" id="button_signout" data-tabindex="21">
		<x-iconic icon="account-logout" />{{ Lang::get("SIGN_OUT") }}
	</a>
</div>
