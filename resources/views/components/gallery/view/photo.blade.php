<template x-if="$store.photo !== undefined">
	<div x-data="photoView(photoFlags, parent_id)"
		x-on:photo-updated.window="refreshPhotoView($store.photo)"
		class="absolute top-0 left-0 w-full flex h-full overflow-hidden bg-black"
		x-show="$store.photo !== undefined"
		x-cloak>
		<header id="lychee_toolbar_container"
			class="absolute top-0 left-0
				px-2 w-full flex-none z-10
				bg-gradient-to-b from-bg-900"
				x-bind:class="isFullscreen ? 'opacity-0 hover:opacity-100' : 'opacity-100 h-14'"
				>
			<div class="flex w-full items-center box-border">
				<x-header.back x-on:click="goTo(null)" />
				<x-header.title />
				@can($this->rights->can_download)
					<x-header.button x-on:click="downloadPhoto()"
						icon="cloud-download" fill="" class="fill-neutral-400"
						/>
				@endif
				@if($this->rights->can_edit)
					<x-header.button x-on:click="photoFlags.isEditOpen = ! photoFlags.isEditOpen" icon="pencil"
						fill=""
						x-bind:class="photoFlags.isEditOpen ? 'fill-primary-500' : 'fill-neutral-400'" />
				@endif
				<x-header.button x-on:click="photoFlags.isDetailsOpen = ! photoFlags.isDetailsOpen"
					icon="info" fill="" class=""
					x-bind:class="photoFlags.isDetailsOpen ? 'fill-primary-500' : 'fill-neutral-400'" />

			</div>
		</header>
		<div class="w-0 flex-auto relative">
			<div id="imageview"
				class="absolute top-0 left-0 w-full h-full bg-black flex items-center justify-center overflow-hidden"
				x-on:click="rotateOverlay()">
				<template x-if="mode === 0">
					{{-- This is a video file: put html5 player --}}
					<video width="auto" height="auto" id='image' controls class='absolute m-auto w-auto h-auto'
						x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'" autobuffer
						{{ $this->photoFlags->can_autoplay ? 'autoplay' : '' }}>
						<source x-bind:src='photo.size_variants.original.url' />
						Your browser does not support the video tag.
					</video>
				</template>
				<template x-if="mode === 1">
					{{-- This is a raw file: put a place holder --}}
					<img id='image' alt='placeholder'
						class='absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat'
						src='{{ URL::asset('img/placeholder.png') }}' />
				</template>
				<template x-if="mode === 2">
					{{-- This is a normal image: medium or original --}}
					<img id='image' alt='medium'
						class='absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat'
						x-bind:src="photo.size_variants.medium.url"
						x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
						x-bind:srcset="srcSetMedium" />
				</template>
				<template x-if="mode === 3">
					<img id='image' alt='big'
						class='absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat'
						x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
						x-bind:style="style"
						x-bind:src='photo.size_variants.original?.url' />
				</template>
				<template x-if="mode === 4">
					{{-- This is a livephoto : medium --}}
					<div id='livephoto' data-live-photo data-proactively-loads-video='true'
						x-bind:data-photo-src="photo.size_variants.medium?.url"
						x-bind:data-video-src="photo.livePhotoUrl" class='absolute m-auto w-auto h-auto'
						x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
						x-bind:style="style">
					</div>
				</template>
				<template x-if="mode === 5">
					{{-- This is a livephoto : full --}}
					<div id='livephoto' data-live-photo data-proactively-loads-video='true'
						x-bind:data-photo-src="photo.size_variants.original?.url"
						x-bind:data-video-src="photo.livePhotoUrl"
						class='absolute m-auto w-auto h-auto'
						x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
						x-bind:style="style">
					</div>
				</template>
				<x-gallery.photo.overlay />
			</div>
			<template x-if="photo.previous_photo_id !== null">
				<x-gallery.photo.next-previous :is_next="false" x-bind:style="previousStyle()" x-on:click="previous()" />
			</template>
			<template x-if="photo.next_photo_id !== null">
				<x-gallery.photo.next-previous :is_next="true" x-bind:style="nextStyle()" x-on:click="next()" />
			</template>
			<template x-if="photo.rights.can_edit">
			<div x-cloak
				class="absolute top-0 h-1/4 w-3/4 xl:w-1/2 left-1/2 -translate-x-1/2 opacity-10 group hover:opacity-100 transition-opacity duration-500 ease-in-out 
					z-20"
					x-show="!photoFlags.isEditOpen">
				<span class="absolute left-1/2 -translate-x-1/2 p-1 min-w-[25%] filter-shadow text-center">
					<x-gallery.photo.button icon="star" class=''
						x-bind:class="photo.is_starred ? 'fill-yellow-500 hover:fill-yellow-100' : 'fill-white hover:fill-yellow-500'"
						x-on:click="toggleStar()" />
					@if ($this->photoFlags->can_rotate)
						<x-gallery.photo.button icon="counterclockwise" class="fill-white hover:fill-primary-500"
							x-on:click="rotatePhotoCCW()" />
						<x-gallery.photo.button icon="clockwise" class="fill-white hover:fill-primary-500"
							x-on:click="rotatePhotoCW()" />
					@endif
					<x-gallery.photo.button icon="transfer" class="fill-white hover:fill-primary-500"
						x-on:click="movePhoto()" />
					<x-gallery.photo.button icon="trash" class="fill-white hover:fill-red-600"
						x-on:click="deletePhoto()" />
				</span>
			</div>
			</template>
		</div>
		<template x-if="photo.rights.can_edit">
			<div class="h-full relative overflow-clip w-0 bg-bg-800 transition-all"
				:class="photoFlags.isEditOpen ? 'w-full' : 'w-0 translate-x-full'">
				<x-gallery.photo.properties />
			</div>
		</template>
		<aside id="lychee_sidebar_container" class="h-full relative overflow-clip transition-all"
			:class="photoFlags.isDetailsOpen ? 'w-[360px]' : 'w-0 translate-x-full'">
			<x-gallery.photo.sidebar />
		</aside>
	</div>
</template>