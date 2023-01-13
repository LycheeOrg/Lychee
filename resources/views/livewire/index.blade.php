<div class="mode-none vflex-container">
	<!-- loading indicator -->
	<div id="lychee_loading" class="vflex-item-rigid"></div>
	<!--
	The application container vertically shares space with the loading indicator.
	If fills the remaining vertical space not taken by the loading indicator.
	The application container contains the left menu and the workbench.
	The application container is also that part which is shaded by the
	background of the modal dialog while the loading indicator and (potential)
	error message is not shaded.
	-->
	<div id="lychee_application_container" class="vflex-item-stretch hflex-container">
	<!-- leftMenu -->
	<div id="lychee_left_menu_container" class="hflex-item-rigid">
		<!-- LIVEWIRE LEFT MENU HERE -->
		{{-- <div id="lychee_left_menu"></div> --}}
	</div>
	<!--
	This container horizontally shares space with the left menu.
	It fills the remaining horizontal space not covered by the left menu.
	-->
	<div class="hflex-item-stretch vflex-container">
		<!--- LIVEWIRE HEADER HERE --->
		<!-- toolbar -->
		{{-- <header id="lychee_toolbar_container" class="vflex-item-rigid">
			<div id="lychee_toolbar_public" class="toolbar">
				<a class="button" id="button_signin"><svg class="iconic"><use xlink:href="#account-login" /></svg></a>
				<a class="header__title"></a>
				<div class="header__search__field">
					<input class="header__search" type="text" name="search">
					<a class="header__clear">&times;</a>
				</div>
				<a class="button button--map-albums"><svg class="iconic"><use xlink:href="#map" /></svg></a>
			</div>
			<div id="lychee_toolbar_albums" class="toolbar">
				<a class="button" id="button_settings"><svg class="iconic"><use xlink:href="#cog" /></svg></a>
				<a class="header__title"></a>
				<div class="header__search__field">
					<input class="header__search" type="text" name="search">
					<a class="header__clear">&times;</a>
				</div>
				<a class="header__divider"></a>
				<a class="button button--map-albums"><svg class="iconic"><use xlink:href="#map" /></svg></a>
				<a class="button button_add"><svg class="iconic"><use xlink:href="#plus" /></svg></a>
			</div>
			<div id="lychee_toolbar_album" class="toolbar">
				<a class="button" id="button_back_home"><svg class="iconic"><use xlink:href="#chevron-left" /></svg></a>
				<a class="header__title"></a>
				<a class="button button--eye" id="button_visibility_album"><svg class="iconic iconic--eye"><use xlink:href="#eye" /></svg></a>
				<a class="button" id="button_sharing_album_users"><svg class="iconic"><use xlink:href="#people" /></svg></a>
				<a class="button button--nsfw" id="button_nsfw_album"><svg class="iconic"><use xlink:href="#warning" /></svg></a>
				<a class="button button--share" id="button_share_album"><svg class="iconic ionicons"><use xlink:href="#share-ion" /></svg></a>
				<a class="button" id="button_archive"><svg class="iconic"><use xlink:href="#cloud-download" /></svg></a>
				<a class="button button--info" id="button_info_album"><svg class="iconic"><use xlink:href="#info" /></svg></a>
				<a class="button button--map" id="button_map_album"><svg class="iconic"><use xlink:href="#map" /></svg></a>
				<a class="button" id="button_move_album"><svg class="iconic"><use xlink:href="#folder" /></svg></a>
				<a class="button" id="button_trash_album"><svg class="iconic"><use xlink:href="#trash" /></svg></a>
				<a class="button" id="button_fs_album_enter"><svg class="iconic"><use xlink:href="#fullscreen-enter" /></svg></a>
				<a class="button" id="button_fs_album_exit"><svg class="iconic"><use xlink:href="#fullscreen-exit" /></svg></a>
				<a class="header__divider"></a>
				<a class="button button_add"><svg class="iconic"><use xlink:href="#plus" /></svg></a>
			</div>
			<div id="lychee_toolbar_photo" class="toolbar">
				<a class="button" id="button_back"><svg class="iconic"><use xlink:href="#chevron-left" /></svg></a>
				<a class="header__title"></a>
				<a class="button button--star" id="button_star"><svg class="iconic"><use xlink:href="#star" /></svg></a>
				<a class="button button--eye" id="button_visibility"><svg class="iconic"><use xlink:href="#eye" /></svg></a>
				<a class="button button--rotate" id="button_rotate_ccwise"><svg class="iconic"><use xlink:href="#counterclockwise" /></svg></a>
				<a class="button button--rotate" id="button_rotate_cwise"><svg class="iconic"><use xlink:href="#clockwise" /></svg></a>
				<a class="button button--share" id="button_share"><svg class="iconic ionicons"><use xlink:href="#share-ion" /></svg></a>
				<a class="button button--info" id="button_info"><svg class="iconic"><use xlink:href="#info" /></svg></a>
				<a class="button button--map" id="button_map"><svg class="iconic"><use xlink:href="#map" /></svg></a>
				<a class="button" id="button_move"><svg class="iconic"><use xlink:href="#folder" /></svg></a>
				<a class="button" id="button_trash"><svg class="iconic"><use xlink:href="#trash" /></svg></a>
				<a class="button" id="button_fs_enter"><svg class="iconic"><use xlink:href="#fullscreen-enter" /></svg></a>
				<a class="button" id="button_fs_exit"><svg class="iconic"><use xlink:href="#fullscreen-exit" /></svg></a>
				<a class="header__divider"></a>
				<a class="button" id="button_more"><svg class="iconic"><use xlink:href="#ellipses" /></svg></a>
			</div>
			<div id="lychee_toolbar_map" class="toolbar">
				<a class="button" id="button_back_map"><svg class="iconic"><use xlink:href="#chevron-left" /></svg></a>
				<a class="header__title"></a>
			</div>
			<div id="lychee_toolbar_config" class="toolbar">
				<a class="button" id="button_close_config"><svg class="iconic"><use xlink:href="#plus" /></svg></a>
				<a class="header__title"></a>
			</div>
		</header> --}}
		<!--
		This container vertically shares space with the toolbar.
		It fills the remaining vertical space not taken by the toolbar.
		It contains the right sidebar and the workbench.
		-->
		<div class="vflex-item-stretch hflex-container">
			<!--
			The workbench horizontally share space with the right
			sidebar.
			It fills the remaining horizontal space not taken be the
			sidebar.
			-->
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
					<div id="lychee_view_content" class="vflex-item-stretch">
						<!-- HERE DISPLAY ALBUMS/PHOTOS--->
					</div>
					<!--
					Footer
					Vertically shares space with the content.
					The height of the footer is always the natural height
					of its child elements
					-->
					<div id="lychee_footer" class="vflex-item-rigid animate animate-up hide_footer">
						<div id="home_socials" class="animate animate-up">
							<a class="socialicons" id="facebook" target="_blank" rel="noopener"></a>
							<a class="socialicons" id="flickr" target="_blank" rel="noopener"></a>
							<a class="socialicons" id="twitter" target="_blank" rel="noopener"></a>
							<a class="socialicons" id="instagram" target="_blank" rel="noopener"></a>
							<a class="socialicons" id="youtube" target="_blank" rel="noopener"></a>
						</div>
						<p class="home_copyright"></p>
						<p class="personal_text"></p>
						<p class="hosted_by">
							<a rel="noopener noreferrer" target="_blank" href="https://LycheeOrg.github.io" tabindex="-1"></a>
						</p>
					</div>
				</div>
				<!--
				Map View
				Does not participate in the flex layout of the
				workbench and does not use the scrolling mechanism of
				the workbench, but overlays the content of the workbench
				and is always as big as the workbench.
				-->
				{{-- <div id="lychee_map_container" class="overlay-container"></div> --}}
				<!--
				Image View
				See comment on map view.
				Moreover, the image view may have a transparent background
				(e.g. on TVs) such that the map view or album shines through.
				-->
				<!-- LIVEWIRE IMAGEVIEW --->
				{{-- <div id="imageview" class="overlay-container"></div> --}}
				<!-- NSFW Warning -->
				<!-- LIVEWIRE NSFW --->
				{{-- <div id="sensitive_warning" class="overlay-container"><h1></h1>
					<p></p>
					<p></p>
				</div> --}}
				<!-- Upload TODO: Figure out how this works -->
				<div id="upload">
					<input id="upload_files" type="file" name="fileElem[]" multiple accept="image/*,video/*,.mov">
					<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml">
				</div>
			</div>
			<!--
			Right sidebar
			We must nest the sidebar into an extra container to avoid
			a re-layout of the sidebar when it comes into or leaves
			view.
			The actual sidebar (`lychee_sidebar`) always has a fixed width,
			but the container shrinks and expands.
			-->
			<div id="lychee_sidebar_container" class="hflex-item-rigid">
				<!-- LIVEWIRE SIDEBAR --->
				{{-- <div id="lychee_sidebar" class="vflex-container">
					<div id="lychee_sidebar_header" class="vflex-item-rigid"><h1></h1></div>
					<!--
					The sidebar wrapper provides a vertical scrolling bar,
					if the information shown inside doesn't fit.
					-->
					<div id="lychee_sidebar_content" class="vflex-item-stretch"></div>
				</div> --}}
			</div>
		</div>
	</div>
</div>
<!--
The frame container vertically shares space with the loading indicator.
If fills the remaining vertical space not taken by the loading indicator.
-->
<!-- LIVEWIRE FRAME --->
{{-- <div id="lychee_frame_container" class="vflex-item-stretch">
	<img id="lychee_frame_bg_image" alt="image background" src=""/>
	<canvas id="lychee_frame_bg_canvas"></canvas>
	<div id="lychee_frame_noise_layer"></div>
	<div id="lychee_frame_image_container"><img id="lychee_frame_image" alt="Random Image" src=""/></div>
	<div id="lychee_frame_shutter"></div>
</div> --}}
</div>