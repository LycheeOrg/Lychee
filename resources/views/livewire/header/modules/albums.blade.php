<div class="header__toolbar header__toolbar--albums header__toolbar--visible">
	@if(!Auth::check())
	<a wire:click="openLoginModal" class="button" id="button_signin" title="{{ Lang::get('SIGN_IN') }}" data-tabindex="{{ Helpers::data_index_r() }}">
		<x-icons.iconic icon='account-login' />
	</a>
	@else
	<a wire:click="openLeftMenu" class="button" id="button_settings" title="{{ Lang::get('SETTINGS') }}" data-tabindex="{{ Helpers::data_index_r() }}">
		<x-icons.iconic icon="cog" />
	</a>
	@endif

	<a class="header__title" data-tabindex="{{ Helpers::data_index() }}">{{ $title }}</a>
	<div class="header__search__field">
		<input class="header__search" type="text" name="search" placeholder="{{ Lang::get('SEARCH') }}" data-tabindex="{{ Helpers::data_index() }}">
		<a class="header__clear">&times;</a>
	</div>
	<a class="header__divider"></a>
	<a class="button button--map-albums" title="{{ Lang::get('DISPLAY_FULL_MAP') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-icons.iconic icon="map" />
	</a>
	@if(Auth::check())
	<a class="button button_add" title="{{ Lang::get('ADD') }}" data-tabindex="{{ Helpers::data_index() }}">
		<x-icons.iconic icon="plus" />
	</a>
	@endif
</div>