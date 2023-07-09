<div>
	<div class="p-9">
		<x-forms.error-message field='wrongLogin' />
		<form class="">
			<div class="mb-4 mx-0">
				<x-forms.inputs.text class="w-full" autocomplete="on"
					data-tabindex="{{ Helpers::data_index() }}"
					placeholder="{{ __('lychee.USERNAME') }}"
					autocapitalize="off"
					wire:model="form.username" />
			</div>
			<div class="my-4 mx-0">
				<x-forms.inputs.password class="w-full" autocomplete="current-password"
					data-tabindex="{{ Helpers::data_index() }}"
					placeholder="{{ __('lychee.PASSWORD') }}"
					wire:model="form.password" />
			</div>
		</form>
		<p class="version text-xs text-right text-neutral-200">
			Lychee
			@if($version !== null) <span class="version-number">{{ $version }}</span> @endif
			@if($is_new_release_available) <x-messages.update-status href="https://github.com/LycheeOrg/Lychee/releases" />
			@elseif($is_git_update_available) <x-messages.update-status href="https://github.com/LycheeOrg/Lychee" />
			@endif
		</p>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-dark-800 rounded-br-md w-full" wire:click="submit">{{ __('lychee.SIGN_IN') }}</x-forms.buttons.action>
	</div>
</div>
