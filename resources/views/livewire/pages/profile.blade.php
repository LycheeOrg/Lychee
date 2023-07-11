<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::PROFILE"
		:title="__('lychee.PROFILE')" />
	<div class="overflow-clip-auto">
		<div class="settings_view max-w-xl text-neutral-400 text-sm mx-auto">
			<livewire:forms.profile.set-login />
			@if($are_notification_active)
			<div class="setting_line">
				<p>
					{{ __('lychee.USER_EMAIL_INSTRUCTION') }}
				</p>
			</div>
			<livewire:forms.profile.set-email />
			@endif
			<livewire:modules.profile.second-factor />
		</div>
	</div>
</div>