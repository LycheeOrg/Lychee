<div class="u2f_view my-10" 
	x-data="registerWebAuthn('{{ __("lychee.U2F_REGISTRATION_SUCCESS") }}', '{{ __("lychee.ERROR_TEXT") }}')"
	>
	{{-- 
	'U2F_NOT_SUPPORTED' => 'U2F not supported. Sorry.',
	'U2F_NOT_SECURE' => 'Environment not secured. U2F not available.',
	'U2F_REGISTER_KEY' => 'Register new device.',
	'U2F_REGISTRATION_SUCCESS' => 'Registration successful!',
	'U2F_CREDENTIALS_DELETED' => 'Credentials deleted!',
	--}}
	<div class="u2f_view_line border-b border-solid border-neutral-600 pb-3 mb-2">
		<p>
			<span class="text text-sm font-bold">
				{{ __('lychee.U2F_CREDENTIALS') }}
			</span>
		</p>
	</div>
	@forelse ($this->credentials as $credential)
		<livewire:forms.profile.manage-second-factor :$credential key="{{ $credential->id }}" />
	@empty
		<div class="u2f_view_line pb-9">
			<p class="single">Credentials list is empty!</p>
		</div>
	@endforelse
	<div class="w-full text-white/80 text-lg font-bold" x-show="isWebAuthnUnavailable()" x-cloak>
		<h1 class="p-3 text-center w-full">{{ __('lychee.U2F_NOT_SECURE') }}</h1>
	</div>
	<div class="u2f_view_line" x-show="!isWebAuthnUnavailable()" x-cloak>
		<x-forms.buttons.create class="rounded-md w-full" x-on:click='register()' >Register new device.</x-forms.buttons.create>
	</div>
</div>