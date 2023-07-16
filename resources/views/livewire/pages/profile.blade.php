<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::PROFILE"
		:title="__('lychee.PROFILE')" />
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<div class="settings_view max-w-xl text-neutral-400 text-sm mx-auto">
			<livewire:forms.profile.set-login />
			@if($are_notification_active)
			<div>
				<p>
					{{ __('lychee.USER_EMAIL_INSTRUCTION') }}
				</p>
			</div>
			<livewire:forms.profile.set-email />
			@endif
			@can(App\Policies\UserPolicy::CAN_USE_2FA, [App\Models\User::class, null])
			<livewire:modules.profile.second-factor />
			@endcan
		</div>
	</div>
</div>