<div class="w-full">
	<!-- toolbar -->
	<livewire:components.header
		:page_mode="App\Enum\Livewire\PageMode::SETTINGS"
		:title="__('lychee.SETTINGS')" />
	<div class="overflow-clip-auto">
		<div class="settings_view w-10/12 max-w-2xl text-neutral-400 text-sm mx-auto">
			<livewire:forms.settings.base.string-setting key="set-dropbox-key" description="DROPBOX_TEXT"
				placeholder="SETTINGS_DROPBOX_KEY" action="DROPBOX_TITLE" name="dropbox_key" />
			<livewire:forms.settings.set-album-sorting-setting />
			<livewire:forms.settings.set-photo-sorting-setting />
			<livewire:forms.settings.set-lang-setting />
			<livewire:forms.settings.set-license-default-setting />
			<livewire:forms.settings.set-layout-setting />
			<livewire:forms.settings.base.boolean-setting key="set-public_search"
				description="PUBLIC_SEARCH_TEXT" name="public_search" />
			<livewire:forms.settings.set-album-decoration-setting />
			<livewire:forms.settings.set-album-decoration-orientation-setting />
			<livewire:forms.settings.set-photo-overlay-setting />
			<livewire:forms.settings.base.boolean-setting key="set-map_display"
				description="MAP_DISPLAY_TEXT" name="map_display" />
			<livewire:forms.settings.base.boolean-setting key="set-map_display_public"
				description="MAP_DISPLAY_PUBLIC_TEXT" name="map_display_public" />
			<livewire:forms.settings.set-map-provider-setting />
			<livewire:forms.settings.base.boolean-setting key="set-map_include_subalbums"
				description="MAP_INCLUDE_SUBALBUMS_TEXT" name="map_include_subalbums" />
			<livewire:forms.settings.base.boolean-setting key="set-location_decoding"
				description="LOCATION_DECODING" name="location_decoding" />
			<livewire:forms.settings.base.boolean-setting key="set-location_show"
				description="LOCATION_SHOW" name="location_show" />
			<livewire:forms.settings.base.boolean-setting key="set-location_show_public"
				description="LOCATION_SHOW_PUBLIC" name="location_show_public" />
			<livewire:forms.settings.base.boolean-setting key="set-nsfw_visible"
				description="NSFW_VISIBLE_TEXT_1" name="nsfw_visible" footer="NSFW_VISIBLE_TEXT_2" />
			<livewire:forms.settings.base.boolean-setting key="set-new_photos_notification"
				description="NEW_PHOTOS_NOTIFICATION" name="new_photos_notification" />

			<div class="my-4">
				<p>
					Personalize CSS:
				</p>
				<textarea id="css" class="p-2 h-28 bg-transparent text-white border border-solid border-neutral-400 resize-y w-full
					hover:border-teal-400
					focus:border-teal-400 focus-visible:outline-none">
				</textarea>
				<div class="basicModal__buttons">
					<a id="basicModal__action_set_css" class="basicModal__button cursor-pointer transition-colors ease-in-out w-full inline-block text-center pt-3 pb-4 font-bold text-teal-400 rounded-md hover:text-white hover:bg-teal-400">Change CSS</a>
				</div>
			</div>

			<div class="my-4">
				<a class="basicModal__button basicModal__button_MORE cursor-pointer transition-colors ease-in-out w-full inline-block text-center pt-3 pb-4 font-bold text-red-800 rounded-md hover:text-white hover:bg-red-800" wire:click="openAllSettings">{{
					__('lychee.MORE') }}</a>
			</div>
		</div>
		<x-footer />
	</div>
</div>