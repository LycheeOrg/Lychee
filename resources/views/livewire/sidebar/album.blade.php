<div class="sidebar__header">
	<h1>{{ Lang::get("ALBUM_ABOUT") }}</h1>
</div>
<div class="sidebar__wrapper">
	<div class="sidebar__divider">
		<h1>{{ Lang::get("ALBUM_BASICS") }}</h1>
	</div>
	<table>
		<tbody>
			<x-atoms.section head='{{ Lang::get("ALBUM_BASICS") }}' >
			<x-atoms.line head='{{ Lang::get("ALBUM_TITLE") }}' :value='$title' />
			<x-atoms.line head='{{ Lang::get("ALBUM_DESCRIPTION") }}' :value='$description' />
			@if ($is_tag_album)
			<x-atoms.line head='{{ Lang::get("ALBUM_SHOW_TAGS") }}' :value='$showtags' />
			@endif
		</tbody>
	</table>
	<div class="sidebar__divider">
		<h1>{{ Lang::get("ALBUM_ALBUM") }}</h1>
	</div>
	<table>
		<tbody>
			<x-atoms.line head='{{ Lang::get("ALBUM_CREATED") }}' :value='$created_at' />
			@if ($children_count > 0)
			<x-atoms.line head='{{ Lang::get("ALBUM_SUBALBUMS") }}' :value='$children_count' />
			@endif
			@if ($photo_count > 0)
			<x-atoms.line head='{{ Lang::get("ALBUM_IMAGES") }}' :value='$photo_count' />
			@endif
			@if ($video_count > 0)
			<x-atoms.line head='{{ Lang::get("ALBUM_VIDEOS") }}' :value='$video_count' />
			@endif
			@if ($photo_count > 0)
				<x-atoms.line head='{{ Lang::get("ALBUM_ORDERING") }}' value='{{ $sorting_col == '' ? Lang::get("DEFAULT") : ($sorting_col + ' ' + $sorting_order) }}' />
			@endif
			@endif
		</tbody>
	</table>
	<div class="sidebar__divider">
		<h1>{{ Lang::get("ALBUM_ALBUM") }}</h1>
	</div>
	<table>
		<tbody>
			<x-atoms.section head='{{ Lang::get("ALBUM_REUSE") }}' >
				<x-atoms.line head='{{ Lang::get("ALBUM_IMAGES") }}' :value='$license' />
			</x-atoms.section>
		</tbody>
	</table>
	@if(Auth::check())
	<div class="sidebar__divider">
		<h1>{{ Lang::get("ALBUM_SHARING") }}</h1>
	</div>
	<table>
		<tbody>
			<x-atoms.line-bool valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
			head='{{ Lang::get("ALBUM_PUBLIC") }}' :value='$this->policy->is_public' />
			<x-atoms.line-bool valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
			head='{{ Lang::get("ALBUM_HIDDEN") }}' :value='$this->policy->is_link_required' />
			<x-atoms.line-bool valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
			head='{{ Lang::get("ALBUM_DOWNLOADABLE") }}' :value='$this->policy->grants_download' />
			<x-atoms.line-bool valueFalse='{{ Lang::get("ALBUM_SHR_NO") }}' valueTrue='{{ Lang::get("ALBUM_SHR_YES") }}'
			head='{{ Lang::get("ALBUM_PASSWORD") }}' :value='$this->policy->is_password_required' />
			<x-atoms.line head='{{ Lang::get("ALBUM_OWNER") }}' :value='$owner_name' />
		</tbody>
	</table>
	@endif
</div>
