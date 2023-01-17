<div class="setSorting">
	<p>
		${sprintf(
			lychee.locale["SORT_ALBUM_BY"],
			<span class="select">
			<select id="settings_albums_sorting_column" name="sorting_albums_column">
				<option value='created_at'>${lychee.locale["SORT_ALBUM_SELECT_1"]}</option>
				<option value='title'>${lychee.locale["SORT_ALBUM_SELECT_2"]}</option>
				<option value='description'>${lychee.locale["SORT_ALBUM_SELECT_3"]}</option>
				<option value='is_public'>${lychee.locale["SORT_ALBUM_SELECT_4"]}</option>
				<option value='max_taken_at'>${lychee.locale["SORT_ALBUM_SELECT_5"]}</option>
				<option value='min_taken_at'>${lychee.locale["SORT_ALBUM_SELECT_6"]}</option>
			</select>
		</span>,
		<span class="select">
			<select id="settings_albums_sorting_order" name="sorting_albums_order">
				<option value='ASC'>${lychee.locale["SORT_ASCENDING"]}</option>
				<option value='DESC'>${lychee.locale["SORT_DESCENDING"]}</option>
			</select>
		</span>
		)}
	</p>
	<p>
		${sprintf(
			lychee.locale["SORT_PHOTO_BY"],
			<span class="select">
			<select id="settings_photos_sorting_column" name="sorting_photos_column">
				<option value='created_at'>${lychee.locale["SORT_PHOTO_SELECT_1"]}</option>
				<option value='taken_at'>${lychee.locale["SORT_PHOTO_SELECT_2"]}</option>
				<option value='title'>${lychee.locale["SORT_PHOTO_SELECT_3"]}</option>
				<option value='description'>${lychee.locale["SORT_PHOTO_SELECT_4"]}</option>
				<option value='is_public'>${lychee.locale["SORT_PHOTO_SELECT_5"]}</option>
				<option value='is_starred'>${lychee.locale["SORT_PHOTO_SELECT_6"]}</option>
				<option value='type'>${lychee.locale["SORT_PHOTO_SELECT_7"]}</option>
			</select>
		  </span>`,
			`<span class="select">
			<select id="settings_photos_sorting_order" name="sorting_photos_order">
				<option value='ASC'>${lychee.locale["SORT_ASCENDING"]}</option>
				<option value='DESC'>${lychee.locale["SORT_DESCENDING"]}</option>
			</select>
		</span>`
		)}
	</p>
</div>