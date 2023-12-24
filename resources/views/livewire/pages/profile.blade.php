<div class="w-full">
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" wire:click="back" />
        <x-header.title>{{ __('lychee.PROFILE') }}</x-header.title>
    </x-header.bar>
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<div class="settings_view max-w-xl text-text-main-400 text-sm mx-auto">
			<livewire:forms.profile.set-login />
			@if($are_notification_active)
			<div>
				<p>
					{{ __('lychee.USER_EMAIL_INSTRUCTION') }}
				</p>
			</div>
			<livewire:forms.profile.set-email />
			@endif
			<livewire:forms.profile.second-factor />
		</div>
	</div>
</div>