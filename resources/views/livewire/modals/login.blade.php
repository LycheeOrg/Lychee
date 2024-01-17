<div>
	<div class="p-9">
		<div class="flex justify-center gap-4 text-2xl">
			@if($this->can_use_2fa)
			<a x-on:click="$wire.dispatch('login-close'); $dispatch('webauthn-open')"
				class="hover:scale-125 transition-all cursor-pointer hover:text-primary-400 mb-6"
				>
				<i class="align-middle fa-solid fa-key"></i>
			</a>
			@endif
			@foreach ($this->available_oauth as $provider)
				<a href="{{ route('oauth-authenticate', ['provider' => $provider]) }}"
					class="hover:scale-125 transition-all cursor-pointer  hover:text-primary-400 mb-6"
					>
					<i @class(["align-middle",
					"fa-brands fa-apple" => $provider === 'apple',
					"fa-brands fa-amazon" => $provider === 'amazon',
					"fa-brands fa-facebook" => $provider === 'facebook',
					"fa-brands fa-github" => $provider === 'github',
					"fa-brands fa-google" => $provider === 'google',
					"fa-brands fa-mastodon" => $provider === 'mastodon',
					"fa-brands fa-microsoft" => $provider === 'microsoft',
					"fa-solid fa-cloud" => $provider === 'nextcloud',
					])></i>
				</a>
			@endforeach
		</div>
		<x-forms.error-message field='wrongLogin' />
		<form class="" wire:submit="submit">
			<div class="mb-4 mx-0">
				<x-forms.inputs.text class="w-full" autocomplete="on"
					autofocus
					x-intersect="$el.focus()"
					placeholder="{{ __('lychee.USERNAME') }}"
					autocapitalize="off"
					wire:model="username" />
			</div>
			<div class="my-4 mx-0">
				<x-forms.inputs.password class="w-full" autocomplete="current-password"
					placeholder="{{ __('lychee.PASSWORD') }}"
					wire:model="password"
					wire:keydown.enter="submit"
					 />
			</div>
		</form>
		<p class="version text-xs text-right text-text-main-200">
			Lychee
			@if($version !== null) <span class="version-number">{{ $version }}</span> @endif
			@if($is_new_release_available) <x-update-status href="https://github.com/LycheeOrg/Lychee/releases" />
			@elseif($is_git_update_available) <x-update-status href="https://github.com/LycheeOrg/Lychee" />
			@endif
		</p>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel
	 		class="border-t border-t-bg-800 rounded-bl-md w-full" x-on:click="$wire.dispatch('login-close')">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
		<x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full" wire:click="submit">{{ __('lychee.SIGN_IN') }}</x-forms.buttons.action>
	</div>
</div>
