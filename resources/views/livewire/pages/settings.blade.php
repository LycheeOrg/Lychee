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
		<div id="lychee_view_content" class="vflex-item-stretch contentZoomIn"><div class="settings_view">
<div class="setLogin">
<form>
  <p>Enter your current password:
	  <input name="oldPassword" class="text" type="password" placeholder="Current Password" value="">
  </p>
  <p>Your credentials will be changed to the following:
	  <input name="username" class="text" type="text" placeholder="New Username" value="">
	  <input name="password" class="text" type="password" placeholder="New Password" value="">
	  <input name="confirm" class="text" type="password" placeholder="Confirm Password" value="">
  </p>
<div class="basicModal__buttons">
	<!--<a id="basicModal__cancel" class="basicModal__button ">Cancel</a>-->
	<a id="basicModal__action_password_change" class="basicModal__button ">Change Login</a>
	<a id="basicModal__action_token" class="basicModal__button ">API Token ...</a>
</div>
</form>
</div>
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

<div class="setDropBox">
  <p>In order to import photos from your Dropbox, you need a valid drop-ins app key from <a href="https://www.dropbox.com/developers/apps/create">their website</a>. Generate yourself a personal key and enter it below:
  <input class="text" name="key" type="text" placeholder="Dropbox API Key" value="">
  </p>
	<div class="basicModal__buttons">
		<a id="basicModal__action_dropbox_change" class="basicModal__button">Set Dropbox Key</a>
	</div>
  </div>

	<div class="setLang">
		<p>
			Change Lychee language for:
			  <span class="select">
				<select id="settings_lang" name="lang">
					<option>简体中文</option><option>繁體中文</option><option>cz</option><option>nl</option><option selected="">en</option><option>fr</option><option>de</option><option>el</option><option>it</option><option>nb-no</option><option>pl</option><option>pt</option><option>ru</option><option>sk</option><option>es</option><option>sv</option><option>vi</option>
				</select>
			  </span>
		</p>
		<div class="basicModal__buttons">
			<a id="basicModal__action_set_lang" class="basicModal__button">Change Language</a>
		</div>
	</div>
<div class="setDefaultLicense">
<p>Default license for new uploads:
<span class="select" style="width:270px">
	<select name="license" id="license">
		<option value="none">None</option>
		<option value="reserved">All Rights Reserved</option>
		<option value="CC0">CC0 - Public Domain</option>
		<option value="CC-BY-1.0">CC Attribution 1.0</option>
		<option value="CC-BY-2.0">CC Attribution 2.0</option>
		<option value="CC-BY-2.5">CC Attribution 2.5</option>
		<option value="CC-BY-3.0">CC Attribution 3.0</option>
		<option value="CC-BY-4.0">CC Attribution 4.0</option>
		<option value="CC-BY-ND-1.0">CC Attribution-NoDerivatives 1.0</option>
		<option value="CC-BY-ND-2.0">CC Attribution-NoDerivatives 2.0</option>
		<option value="CC-BY-ND-2.5">CC Attribution-NoDerivatives 2.5</option>
		<option value="CC-BY-ND-3.0">CC Attribution-NoDerivatives 3.0</option>
		<option value="CC-BY-ND-4.0">CC Attribution-NoDerivatives 4.0</option>
		<option value="CC-BY-SA-1.0">CC Attribution-ShareAlike 1.0</option>
		<option value="CC-BY-SA-2.0">CC Attribution-ShareAlike 2.0</option>
		<option value="CC-BY-SA-2.5">CC Attribution-ShareAlike 2.5</option>
		<option value="CC-BY-SA-3.0">CC Attribution-ShareAlike 3.0</option>
		<option value="CC-BY-SA-4.0">CC Attribution-ShareAlike 4.0</option>
		<option value="CC-BY-NC-1.0">CC Attribution-NonCommercial 1.0</option>
		<option value="CC-BY-NC-2.0">CC Attribution-NonCommercial 2.0</option>
		<option value="CC-BY-NC-2.5">CC Attribution-NonCommercial 2.5</option>
		<option value="CC-BY-NC-3.0">CC Attribution-NonCommercial 3.0</option>
		<option value="CC-BY-NC-4.0">CC Attribution-NonCommercial 4.0</option>
		<option value="CC-BY-NC-ND-1.0">CC Attribution-NonCommercial-NoDerivatives 1.0</option>
		<option value="CC-BY-NC-ND-2.0">CC Attribution-NonCommercial-NoDerivatives 2.0</option>
		<option value="CC-BY-NC-ND-2.5">CC Attribution-NonCommercial-NoDerivatives 2.5</option>
		<option value="CC-BY-NC-ND-3.0">CC Attribution-NonCommercial-NoDerivatives 3.0</option>
		<option value="CC-BY-NC-ND-4.0">CC Attribution-NonCommercial-NoDerivatives 4.0</option>
		<option value="CC-BY-NC-SA-1.0">CC Attribution-NonCommercial-ShareAlike 1.0</option>
		<option value="CC-BY-NC-SA-2.0">CC Attribution-NonCommercial-ShareAlike 2.0</option>
		<option value="CC-BY-NC-SA-2.5">CC Attribution-NonCommercial-ShareAlike 2.5</option>
		<option value="CC-BY-NC-SA-3.0">CC Attribution-NonCommercial-ShareAlike 3.0</option>
		<option value="CC-BY-NC-SA-4.0">CC Attribution-NonCommercial-ShareAlike 4.0</option>
	</select>
</span>
<br>
<a href="https://creativecommons.org/choose/" target="_blank">Need help choosing?</a>
</p>
<div class="basicModal__buttons">
	<a id="basicModal__action_set_license" class="basicModal__button">Set License</a>
</div>
</div>

<div class="setLayout">
<p>Layout of photos:
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

<div class="setPublicSearch">
<p>Public search allowed:
<label class="switch">
  <input id="PublicSearch" type="checkbox" name="public_search">
  <span class="slider round"></span>
</label>
</p>
</div>

<div class="setAlbumDecoration">
<p>Album decorations:
<span class="select" style="width:270px">
	<select name="album_decoration" id="AlbumDecorationType">
		<option value="none">None</option>
		<option value="layers">Sub-album marker</option>
		<option value="album">Number of sub-albums</option>
		<option value="photo">Number of photos</option>
		<option value="all">Number of sub-albums and photos</option>
	</select>
</span>
</p><p>Orientation of album decorations:
<span class="select" style="width:270px">
	<select name="album_decoration_orientation" id="AlbumDecorationOrientation">
		<option value="row">Horizontal (photos, albums)</option>
		<option value="row-reverse">Horizontal (albums, photos)</option>
		<option value="column">Vertical (top photos, albums)</option>
		<option value="column-reverse">Vertical (top albums, photos)</option>
	</select>
</span>
</p><div class="basicModal__buttons">
	<a id="basicModal__action_set_album_decoration" class="basicModal__button">Set album decorations</a>
</div>
</div>

<div class="setOverlayType">
<p>Photo overlay:
<span class="select" style="width:270px">
	<select name="image_overlay_type" id="ImgOverlayType">
		<option value="exif">EXIF data</option>
		<option value="desc">Description</option>
		<option value="date">Date taken</option>
		<option value="none">None</option>
	</select>
</span>
</p><div class="basicModal__buttons">
	<a id="basicModal__action_set_overlay_type" class="basicModal__button">Set Overlay</a>
</div>
</div>

<div class="setMapDisplay">
<p>Enable maps (provided by OpenStreetMap):
<label class="switch">
  <input id="MapDisplay" type="checkbox" name="map_display">
  <span class="slider round"></span>
</label>
</p>
</div>

<div class="setMapDisplayPublic">
<p>Enable maps for public albums (provided by OpenStreetMap):
<label class="switch">
	<input id="MapDisplayPublic" type="checkbox" name="map_display_public">
	<span class="slider round"></span>
</label>
</p>
</div>

<div class="setMapProvider">
<p>Provider of OpenStreetMap tiles:
<span class="select" style="width:270px">
	<select name="map_provider" id="MapProvider">
		<option value="Wikimedia">Wikimedia</option>
		<option value="OpenStreetMap.org">OpenStreetMap.org (no HiDPI)</option>
		<option value="OpenStreetMap.de">OpenStreetMap.de (no HiDPI)</option>
		<option value="OpenStreetMap.fr">OpenStreetMap.fr (no HiDPI)</option>
		<option value="RRZE">University of Erlangen, Germany (only HiDPI)</option>
	</select>
</span>
</p><div class="basicModal__buttons">
	<a id="basicModal__action_set_map_provider" class="basicModal__button">Set OpenStreetMap tiles provider</a>
</div>
</div>

<div class="setMapIncludeSubAlbums">
<p>Include photos of subalbums on map:
<label class="switch">
  <input id="MapIncludeSubAlbums" type="checkbox" name="map_include_subalbums">
  <span class="slider round"></span>
</label>
</p>
</div>

<div class="setLocationDecoding">
<p>Decode GPS data into location name
<label class="switch">
  <input id="LocationDecoding" type="checkbox" name="location_decoding">
  <span class="slider round"></span>
</label>
</p>
</div>

<div class="setLocationShow">
<p>Show location name
<label class="switch">
  <input id="LocationShow" type="checkbox" name="location_show">
  <span class="slider round"></span>
</label>
</p>
</div>

<div class="setLocationShowPublic">
<p>Show location name for public mode
<label class="switch">
	<input id="LocationShowPublic" type="checkbox" name="location_show_public">
	<span class="slider round"></span>
</label>
</p>
</div>

<div class="setNSFWVisible">
<p>Make Sensitive albums visible by default.
<label class="switch">
  <input id="NSFWVisible" type="checkbox" name="nsfw_visible">
  <span class="slider round"></span>
</label></p>
<p>If the album is public, it is still accessible, just hidden from the view and <b>can be revealed by pressing <kbd>H</kbd></b>.
</p>
</div>

<div class="setNewPhotosNotification">
<p>Send new photos notification emails.
<label class="switch">
	<input id="NewPhotosNotification" type="checkbox" name="new_photos_notification">
	<span class="slider round"></span>
</label>
</p>
</div>

<div class="setCSS">
<p>Personalize CSS:</p>
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
		<!--
		Footer
		Vertically shares space with the content.
		The height of the footer is always the natural height
		of its child elements
		-->
		<div id="lychee_footer" class="vflex-item-rigid animate animate-up">
			<div id="home_socials" class="animate animate-up" style="display: none;"><a class="socialicons" id="facebook" target="_blank" rel="noopener"></a><a class="socialicons" id="flickr" target="_blank" rel="noopener"></a><a class="socialicons" id="twitter" target="_blank" rel="noopener"></a><a class="socialicons" id="instagram" target="_blank" rel="noopener"></a><a class="socialicons" id="youtube" target="_blank" rel="noopener"></a></div>
			<p class="home_copyright">All images on this website are subject to copyright by John Smith © 2019</p>
			<p class="personal_text"></p>
			<p class="hosted_by"><a rel="noopener noreferrer" target="_blank" href="https://LycheeOrg.github.io" tabindex="-1">Hosted with Lychee</a></p>
		</div>
	</div>
</div>