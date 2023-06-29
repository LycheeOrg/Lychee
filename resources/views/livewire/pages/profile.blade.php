<x-view.content :mode="$mode" :title="__('lychee.PROFILE')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<div class="settings_view">
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
</x-view.content>