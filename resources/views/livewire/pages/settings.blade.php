<x-view.content :mode="$mode" :title="__('lychee.SETTINGS')">
	<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
		<div class="settings_view">
			<livewire:forms.settings.base.string-setting
				key="set-dropbox-key"
				description="DROPBOX_TEXT"
				placeholder="SETTINGS_DROPBOX_KEY"
				action="DROPBOX_TITLE"
				name="dropbox_key"
			/>
			<livewire:forms.settings.set-album-sorting-setting />
			<livewire:forms.settings.set-photo-sorting-setting />
			<livewire:forms.settings.set-lang-setting />
			<livewire:forms.settings.set-license-default-setting />
			<livewire:forms.settings.set-layout-setting />
			<livewire:forms.settings.base.boolean-setting
				key="set-public_search"
				description="PUBLIC_SEARCH_TEXT"
				name="public_search"
			/>
			<livewire:forms.settings.set-album-decoration-setting />
			<livewire:forms.settings.set-album-decoration-orientation-setting />
			<livewire:forms.settings.set-photo-overlay-setting />
			<livewire:forms.settings.base.boolean-setting
				key="set-map_display"
				description="MAP_DISPLAY_TEXT"
				name="map_display"
			/>
			<livewire:forms.settings.base.boolean-setting
				key="set-map_display_public"
				description="MAP_DISPLAY_PUBLIC_TEXT"
				name="map_display_public"
			/>
			<livewire:forms.settings.set-map-provider-setting />
			<livewire:forms.settings.base.boolean-setting
				key="set-map_include_subalbums"
				description="MAP_INCLUDE_SUBALBUMS_TEXT"
				name="map_include_subalbums"
			/>
			<livewire:forms.settings.base.boolean-setting
				key="set-location_decoding"
				description="LOCATION_DECODING"
				name="location_decoding"
			/>
			<livewire:forms.settings.base.boolean-setting
				key="set-location_show"
				description="LOCATION_SHOW"
				name="location_show"
			/>
			<livewire:forms.settings.base.boolean-setting
				key="set-location_show_public"
				description="LOCATION_SHOW_PUBLIC"
				name="location_show_public"
			/>
			<livewire:forms.settings.base.boolean-setting
				key="set-nsfw_visible"
				description="NSFW_VISIBLE_TEXT_1"
				name="nsfw_visible"
				footer="NSFW_VISIBLE_TEXT_2"
			/>
			<livewire:forms.settings.base.boolean-setting
				key="set-new_photos_notification"
				description="NEW_PHOTOS_NOTIFICATION"
				name="new_photos_notification"
			/>

			<div class="setCSS">
				<p>
					Personalize CSS:
				</p>
				<textarea id="css">
			</textarea>
				<div class="basicModal__buttons">
					<a id="basicModal__action_set_css" class="basicModal__button">Change CSS</a>
				</div>
			</div>

			<div class="setCSS">
				<a class="basicModal__button basicModal__button_MORE" wire:click="openAllSettings">{{ __('lychee.MORE') }}</a>
			</div>
		</div>
	</div>
</x-view.content>