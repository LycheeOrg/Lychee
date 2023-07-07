<div>
	<div class="basicModal__content p-9">
		<x-forms.error-message field='wrongLogin' />
		<form class="">
			<div class="mb-4 mx-0">
				<input 
				class="w-full border-b border-b-dark-600 py-1 px-0.5 bg-transparent shadow-black text-white
				hover:border-b-sky-400 focus:border-b-sky-400"
				@class(['text', 'error' => $errors->has('form.username')])
				autocomplete="on" type="text" placeholder="{{ __('lychee.USERNAME') }}" autocapitalize="off"
				data-tabindex="{{ Helpers::data_index() }}" wire:model="form.username">
			</div>
			<div class="my-4 mx-0">
				<input
				class="w-full border-b border-b-dark-600 py-1 px-0.5 bg-transparent shadow-black text-white
				hover:border-b-red-700 focus:border-b-red-700"
				@class(['text', 'error' => $errors->has('form.password') || $errors->has('wrongLogin')])
				autocomplete="current-password" type="password" placeholder="{{ __('lychee.PASSWORD') }}"
				data-tabindex="{{ Helpers::data_index() }}" wire:model="form.password">
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
	<div class="basicModal__buttons flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md " wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-dark-800 rounded-br-md " wire:click="submit">{{ __('lychee.SIGN_IN') }}</x-forms.buttons.action>
	</div>
</div>
