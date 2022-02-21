<div class="header__toolbar header__toolbar--albums header__toolbar--visible">
	@if(!AccessControl::is_logged_in())
	<a class="button" id="button_signin" title="{{ Lang::get('SIGN_IN') }}" data-tabindex="{{ Helpers::data_index_r() }}" wire:click="login">
		<x-iconic icon='account-login' />
	</a>
	@else
	<a class="button" id="button_settings" title="{{ Lang::get('SETTINGS') }}" data-tabindex="{{ Helpers::data_index_r() }}" wire:click="openLeftMenu">
		<x-iconic icon="cog" />
	</a>
	@endif

	<a class="header__title" data-tabindex="{{ Helpers::data_index() }}">{{ $title }}</a>
	<div class="header__search__field">
		<input class="header__search" type="text" name="search" placeholder="{{ Lang::get('SEARCH') }}" data-tabindex="{{ Helpers::data_index() }}">
		<a class="header__clear">&times;</a>
	</div>
	<a class="header__divider"></a>
	<a class="button button--map-albums" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="map" />
	</a>
	@if(AccessControl::is_logged_in())
	<a class="button button_add" title="{{ Lang::get('ADD') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-iconic icon="plus" />
	</a>
	@endif
</div>