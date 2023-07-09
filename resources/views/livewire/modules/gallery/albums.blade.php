<div class="overflow-clip-auto">
	@if ($smartalbums->isEmpty() && $albums->isEmpty() && $shared_albums->isEmpty())
	<div>
		<div wire:init='openLoginModal'>
			<x-icons.iconic icon="eye" />
			<p>{{ __('lychee.VIEW_NO_PUBLIC_ALBUMS') }}</p>
		</div>
	</div>
	@else
	<div class="flex flex-wrap flex-auto flex-shrink-0 w-full justify-start">
		{{-- <div class=""> --}}
			<!-- test comment-->
		@if($smartalbums->count() > 0)
			<x-gallery.divider>
				{{ __('lychee.SMART_ALBUMS') }}
			</x-gallery.divider>
			@foreach ($smartalbums as $album)
				<x-gallery.album :data="$album" />
			@endforeach
			@if($albums->count() > 0)
			<x-gallery.divider>
				{{ __('lychee.ALBUMS') }}
			</x-gallery.divider>
			@endif
		@endif

		@if($albums->count() > 0)
			@foreach ($albums as $album)
				<x-gallery.album :data="$album" />
			@endforeach
		@endif

		@if($shared_albums->count() > 0)
		<x-gallery.divider>
			{{ __('lychee.SHARED_ALBUMS') }}
		</x-gallery.divider>
			@foreach ($shared_albums as $album)
				<x-gallery.album :data="$album" />
			@endforeach
		@endif
	</div>
	@endif
	<x-footer />
</div>