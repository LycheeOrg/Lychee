<div id="lychee_sidebar" class="vflex-container">
	<div id="lychee_sidebar_header" class="vflex-item-rigid">
		<h1>{{ __("lychee.ALBUM_ABOUT") }}</h1>
	</div>
	<div id="lychee_sidebar_content" class="vflex-item-stretch">
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_BASICS") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-atoms.line head='{{ __("lychee.ALBUM_TITLE") }}'>
					{{ $title }}
				</x-atoms.line>
				<x-atoms.line head='{{ __("lychee.ALBUM_DESCRIPTION") }}'>
					{{ $description }}
				</x-atoms.line>
				@if ($is_tag_album)
				<x-atoms.line head='{{ __("lychee.ALBUM_SHOW_TAGS") }}'>
					{!!
						implode(
							'<span class="attr_showtags_separator">, </span>',
							array_map(
								fn($v) => '<span class="attr_showtags search">' . htmlspecialchars($v) . '</span>',
								$showtags
							)
						)
					!!}
				</x-atoms.line>
				@endif
			</tbody>
		</table>
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_ALBUM") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-atoms.line head='{{ __("lychee.ALBUM_CREATED") }}'>
					{{ $created_at }}
				</x-atoms.line>
				@if ($children_count > 0)
				<x-atoms.line head='{{ __("lychee.ALBUM_SUBALBUMS") }}'>
					{{ $children_count }}
				</x-atoms.line>
				@endif
				@if ($photo_count > 0)
				<x-atoms.line head='{{ __("lychee.ALBUM_IMAGES") }}'>
					{{ $photo_count }}
				</x-atoms.line>
				@endif
				@if ($video_count > 0)
				<x-atoms.line head='{{ __("lychee.ALBUM_VIDEOS") }}'>
					{{ $video_count }}
				</x-atoms.line>
				@endif
				<x-atoms.line head='{{ __("lychee.ALBUM_ORDERING") }}'>
					{{ $sorting_col === '' || $sorting_col === null  ? __("lychee.DEFAULT") : ($sorting_col + ' ' + $sorting_order) }}
				</x-atoms.line>
			</tbody>
		</table>
		@if (!$is_tag_album)
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_ALBUM") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-atoms.line head='{{ __("lychee.ALBUM_LICENSE") }}' :value='$license' />
			</tbody>
		</table>
		@endif
		@if(Auth::check())
		<div class="sidebar__divider">
			<h1>{{ __("lychee.ALBUM_SHARING") }}</h1>
		</div>
		<table aria-hidden="true"> {{-- Refactor me later to not use table --}}
			<tbody>
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_PUBLIC") }}' :value='$this->policy->is_public' />
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_HIDDEN") }}' :value='$this->policy->is_link_required' />
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_DOWNLOADABLE") }}' :value='$this->policy->grants_download' />
				<x-atoms.line-bool valueFalse='{{ __("lychee.ALBUM_SHR_NO") }}' valueTrue='{{ __("lychee.ALBUM_SHR_YES") }}'
				head='{{ __("lychee.ALBUM_PASSWORD") }}' :value='$this->policy->is_password_required' />
				<x-atoms.line head='{{ __("lychee.ALBUM_OWNER") }}'>
					{{ $owner_name }}
				</x-atoms.line>
			</tbody>
		</table>
		@endif
	</div>
</div>