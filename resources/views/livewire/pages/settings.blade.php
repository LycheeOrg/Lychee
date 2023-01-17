<div class="hflex-item-stretch vflex-container">
	<!-- toolbar -->
	<livewire:components.header :page_mode="$mode" :title="Lang::get('SETTINGS')" />

	<!--
		This container vertically shares space with the toolbar.
		It fills the remaining vertical space not taken by the toolbar.
		It contains the right sidebar and the workbench.
	-->
	<div class="vflex-item-stretch hflex-container">
		<div id="lychee_workbench_container" class="hflex-item-stretch">
			<!--
			The view container covers the entire workbench and
			contains the content and the footer.
			It provides a vertical scroll bar if the content
			grows too large.
			Opposed to the map view and image view the view container
			holds views which are scrollable (e.g. settings,
			album listings, etc.)
			-->
			<div id="lychee_view_container" class="vflex-container">
				<!--
				Content
				Vertically shares space with the footer.
				The minimum height is set such the footer is positioned
				at the bottom even if the content is smaller.
				-->
				<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn">
					<div class="settings_view">

						<livewire:forms.settings.set-login-setting />

						<div class="setSorting">
							<p>
								Sort albums by <span class="select">
									<select id="settings_albums_sorting_column" name="sorting_albums_column">
										<option value="created_at">Creation Time</option>
										<option value="title">Title</option>
										<option value="description">Description</option>
										<option value="is_public">Public</option>
										<option value="max_taken_at">Latest Take Date</option>
										<option value="min_taken_at">Oldest Take Date</option>
									</select>
								</span> in an <span class="select">
									<select id="settings_albums_sorting_order" name="sorting_albums_order">
										<option value="ASC">Ascending</option>
										<option value="DESC">Descending</option>
									</select>
								</span> order.
							</p>
							<p>
								Sort photos by <span class="select">
									<select id="settings_photos_sorting_column" name="sorting_photos_column">
										<option value="created_at">Upload Time</option>
										<option value="taken_at">Take Date</option>
										<option value="title">Title</option>
										<option value="description">Description</option>
										<option value="is_public">Public</option>
										<option value="is_starred">Star</option>
										<option value="type">Photo Format</option>
									</select>
								</span> in an <span class="select">
									<select id="settings_photos_sorting_order" name="sorting_photos_order">
										<option value="ASC">Ascending</option>
										<option value="DESC">Descending</option>
									</select>
								</span> order.
							</p>
							<div class="basicModal__buttons">
								<!--<a id="basicModal__cancel" class="basicModal__button ">Cancel</a>-->
								<a id="basicModal__action_sorting_change" class="basicModal__button ">Change Sorting</a>
							</div>
						</div>

						<livewire:forms.settings.string-setting
							key="set-dropbox-key"
							description="DROPBOX_TEXT"
							placeholder="SETTINGS_DROPBOX_KEY"
							action="DROPBOX_TITLE"
							name="dropbox_key"
						/>
						<livewire:forms.settings.set-lang-setting />
						<livewire:forms.settings.set-license-default-setting />
						<div class="setLayout">
							<p>
								Layout of photos:
								<span class="select" style="width:270px">
									<select name="layout" id="layout">
										<option value="0">Square thumbnails</option>
										<option value="1">With aspect, justified</option>
										<option value="2">With aspect, unjustified</option>
									</select>
								</span>
							</p>
							<div class="basicModal__buttons">
								<a id="basicModal__action_set_layout" class="basicModal__button">Change layout</a>
							</div>
						</div>
						<livewire:forms.settings.boolean-setting
							key="set-public_search"
							description="PUBLIC_SEARCH_TEXT"
							name="public_search"
						/>

						<div class="setAlbumDecoration">
							<p>
								Album decorations:
								<span class="select" style="width:270px">
									<select name="album_decoration" id="AlbumDecorationType">
										<option value="none">None</option>
										<option value="layers">Sub-album marker</option>
										<option value="album">Number of sub-albums</option>
										<option value="photo">Number of photos</option>
										<option value="all">Number of sub-albums and photos</option>
									</select>
								</span>
							</p>
							<p>
								Orientation of album decorations:
								<span class="select" style="width:270px">
									<select name="album_decoration_orientation" id="AlbumDecorationOrientation">
										<option value="row">Horizontal (photos, albums)</option>
										<option value="row-reverse">Horizontal (albums, photos)</option>
										<option value="column">Vertical (top photos, albums)</option>
										<option value="column-reverse">Vertical (top albums, photos)</option>
									</select>
								</span>
							</p>
							<div class="basicModal__buttons">
								<a id="basicModal__action_set_album_decoration" class="basicModal__button">Set album
									decorations</a>
							</div>
						</div>

						<div class="setOverlayType">
							<p>
								Photo overlay:
								<span class="select" style="width:270px">
									<select name="image_overlay_type" id="ImgOverlayType">
										<option value="exif">EXIF data</option>
										<option value="desc">Description</option>
										<option value="date">Date taken</option>
										<option value="none">None</option>
									</select>
								</span>
							</p>
							<div class="basicModal__buttons">
								<a id="basicModal__action_set_overlay_type" class="basicModal__button">Set Overlay</a>
							</div>
						</div>

						<livewire:forms.settings.boolean-setting
							key="set-map_display"
							description="MAP_DISPLAY_TEXT"
							name="map_display"
						/>
						<livewire:forms.settings.boolean-setting
							key="set-map_display_public"
							description="MAP_DISPLAY_PUBLIC_TEXT"
							name="map_display_public"
						/>

						<div class="setMapProvider">
							<p>
								Provider of OpenStreetMap tiles:
								<span class="select" style="width:270px">
									<select name="map_provider" id="MapProvider">
										<option value="Wikimedia">Wikimedia</option>
										<option value="OpenStreetMap.org">OpenStreetMap.org (no HiDPI)</option>
										<option value="OpenStreetMap.de">OpenStreetMap.de (no HiDPI)</option>
										<option value="OpenStreetMap.fr">OpenStreetMap.fr (no HiDPI)</option>
										<option value="RRZE">University of Erlangen, Germany (only HiDPI)</option>
									</select>
								</span>
							</p>
							<div class="basicModal__buttons">
								<a id="basicModal__action_set_map_provider" class="basicModal__button">Set OpenStreetMap
									tiles provider</a>
							</div>
						</div>

						<livewire:forms.settings.boolean-setting
							key="set-map_include_subalbums"
							description="MAP_INCLUDE_SUBALBUMS_TEXT"
							name="map_include_subalbums"
						/>
						<livewire:forms.settings.boolean-setting
							key="set-location_decoding"
							description="LOCATION_DECODING"
							name="location_decoding"
						/>
						<livewire:forms.settings.boolean-setting
							key="set-location_show"
							description="LOCATION_SHOW"
							name="location_show"
						/>
						<livewire:forms.settings.boolean-setting
							key="set-location_show_public"
							description="LOCATION_SHOW_PUBLIC"
							name="location_show_public"
						/>
						<livewire:forms.settings.boolean-setting
							key="set-location_show_public"
							description="LOCATION_SHOW_PUBLIC"
							name="location_show_public"
						/>
						<livewire:forms.settings.boolean-setting
							key="set-nsfw_visible"
							description="NSFW_VISIBLE_TEXT_1"
							name="nsfw_visible"
							footer="NSFW_VISIBLE_TEXT_2"
						/>
						<livewire:forms.settings.boolean-setting
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
							<a id="basicModal__action_more" class="basicModal__button basicModal__button_MORE">More</a>
						</div>
					</div>
				</div>

				<livewire:components.footer />

			</div>
		</div>
		<livewire:components.base.modal />
	</div>