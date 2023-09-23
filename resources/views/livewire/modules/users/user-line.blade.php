<div class="users_view_line w-full flex my-1">
	<p class="w-full flex">
		<x-forms.inputs.text class="w-full mt-4" wire:model.live="username" type="text" placeholder="{{ __('lychee.LOGIN_USERNAME') }}" />
		<x-forms.inputs.important class="w-full mt-4" wire:model.live="password" type="text" placeholder="{{ __('lychee.LOGIN_PASSWORD') }}" />
		<x-forms.tickbox class="mt-2.5" title="{{ __('lychee.ALLOW_UPLOADS') }}" wire:model.live='may_upload' />
		<x-forms.tickbox class="mt-2.5" title="{{ __('lychee.ALLOW_USER_SELF_EDIT') }}" wire:model.live='may_edit_own_settings' />
	</p>
	@if($this->hasChanged)
	<x-forms.buttons.action wire:click='save' class="w-1/6 rounded-r-md h-11" >{{ __('lychee.SAVE') }}</x-forms.buttons.action>
	@elseif($user->may_administrate !== true)
	<x-forms.buttons.danger wire:click='$parent.delete({{ $id }})' class="w-1/6 rounded-r-md h-11" >{{ __('lychee.DELETE') }}</x-forms.buttons.action>
	@else 
	<a class="inline-block h-10 invisible w-1/6 pt-2 pb-4 border-t border-t-dark-800">{{ __('lychee.SAVE') }}</a>
	@endif
</div>