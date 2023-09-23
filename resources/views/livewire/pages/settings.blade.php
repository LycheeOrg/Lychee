<div class="w-full">
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" />
        <x-header.title>{{ __('lychee.SETTINGS') }}</x-header.title>
    </x-header.bar>
	<div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
		<div class="settings_view w-10/12 max-w-xl text-neutral-400 text-sm mx-auto">
			<livewire:forms.settings.base.string-setting key="set-dropbox-key" description="DROPBOX_TEXT"
				placeholder="SETTINGS_DROPBOX_KEY" action="DROPBOX_TITLE" name="dropbox_key" />
			<livewire:forms.settings.set-album-sorting-setting />
			<livewire:forms.settings.set-photo-sorting-setting />
			<livewire:forms.settings.set-lang-setting />
			<livewire:forms.settings.set-license-default-setting />
			<div class="mb-4 -mt-4"><p>
				<a href="https://creativecommons.org/choose/" class="text-neutral-200 hover:text-white border-b border-dashed border-neutral-400" target="_blank">{{ __('lychee.ALBUM_LICENSE_HELP') }}</a>
			</p></div>
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
				<p>{{ __('lychee.CSS_TEXT') }}</p>
				<textarea id="css" class="p-2 h-28 bg-transparent text-white border border-solid border-neutral-400 resize-y w-full
					hover:border-sky-400
					focus:border-sky-400 focus-visible:outline-none">
				</textarea>
				<div class="basicModal__buttons">
					<x-forms.buttons.action class="rounded-md w-full" >Change CSS</x-forms.buttons.action>
				</div>
			</div>

			<div class="my-4">
				<x-forms.buttons.danger class="rounded-md w-full" wire:navigate href="{{ route('all-settings') }}">{{ __('lychee.MORE') }}</x-forms.buttons.danger>
			</div>
		</div>
	</div>
</div>