<div class="u2f_view my-10">
	{{-- 'U2F_NOT_SUPPORTED' => 'U2F not supported. Sorry.',
	'U2F_NOT_SECURE' => 'Environment not secured. U2F not available.',
	'U2F_REGISTER_KEY' => 'Register new device.',
	'U2F_REGISTRATION_SUCCESS' => 'Registration successful!',
	'U2F_AUTHENTIFICATION_SUCCESS' => 'Authentication successful!',
	'U2F_CREDENTIALS' => 'Credentials',
	'U2F_CREDENTIALS_DELETED' => 'Credentials deleted!', --}}
	<div class="u2f_view_line border-b border-solid border-neutral-600 pb-3 mb-2">
		<p>
			<span class="text text-sm font-bold">
				{{ __('lychee.U2F_CREDENTIALS') }}
			</span>
		</p>
	</div>
	@forelse ($credentials as $credential)
		<livewire:forms.profile.manage-second-factor :credential="$credential" key="{{ $credential->id }}" />
	@empty
		<div class="u2f_view_line">
			<p class="single">Credentials list is empty!</p>
		</div>
	@endforelse
	<div class="u2f_view_line">
		<x-forms.buttons.create class="rounded-md w-full" >Register new device.</x-forms.buttons.create>
	</div>
</div>