<div id="lychee_sidebar" class="vflex-container">
	<div id="lychee_sidebar_header" class="vflex-item-rigid">
		<h1>{{ __("lychee.ALBUM_ABOUT") }}</h1>
	</div>
	<div id="lychee_sidebar_content" class="vflex-item-stretch">
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_BASICS") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.ALBUM_TITLE") }}' :value='$title' />
				<x-atoms.line head='{{ __("lychee.ALBUM_DESCRIPTION") }}' :value='$description' />
				@if ($is_tag_album)
				<x-atoms.line head='{{ __("lychee.ALBUM_SHOW_TAGS") }}' :value='$showtags' />
				@endif
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_ALBUM") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.ALBUM_CREATED") }}' :value='$created_at' />
				@if ($children_count > 0)
				<x-atoms.line head='{{ __("lychee.ALBUM_SUBALBUMS") }}' :value='$children_count' />
				@endif
				@if ($photo_count > 0)
				<x-atoms.line head='{{ __("lychee.ALBUM_IMAGES") }}' :value='$photo_count' />
				@endif
				@if ($video_count > 0)
				<x-atoms.line head='{{ __("lychee.ALBUM_VIDEOS") }}' :value='$video_count' />
				@endif
				@if ($photo_count > 0)
					<x-atoms.line head='{{ __("lychee.ALBUM_ORDERING") }}' value='{{ $sorting_col == '' ? __("lychee.DEFAULT") : ($sorting_col + ' ' + $sorting_order) }}' />
				@endif
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_ALBUM") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line head='{{ __("lychee.ALBUM_IMAGES") }}' :value='$license' />
			</tbody>
		</table>
		@if(Auth::check())
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_SHARING") }}</h1>
		</div>
		<table>
			<tbody>
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_PUBLIC") }}' :value='$this->policy->is_public' />
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_HIDDEN") }}' :value='$this->policy->is_link_required' />
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_DOWNLOADABLE") }}' :value='$this->policy->grants_download' />
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_PASSWORD") }}' :value='$this->policy->is_password_required' />
				<x-atoms.line head='{{ __("lychee.ALBUM_OWNER") }}' :value='$owner_name' />
			</tbody>
		</table>
		@endif
	</div>
</div>