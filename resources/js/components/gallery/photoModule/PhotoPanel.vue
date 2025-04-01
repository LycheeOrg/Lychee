<template>
	<div
		id="shutter"
		:class="{
			'absolute w-screen h-dvh bg-surface-950 transition-opacity duration-1000 ease-in-out top-0 left-0': true,
			'z-50 opacity-0 pointer-events-none': is_slideshow_active,
		}"
	></div>
	<div class="absolute top-0 left-0 w-full flex h-full overflow-hidden bg-black">
		<PhotoHeader :photo="props.photo" @toggle-slide-show="emits('toggleSlideShow')" @go-back="emits('goBack')" />
		<div class="w-0 flex-auto relative">
			<div
				id="imageview"
				class="absolute top-0 left-0 w-full h-full bg-black flex items-center justify-center overflow-hidden"
				@click="emits('rotateOverlay')"
				ref="swipe"
			>
				<!--  This is a video file: put html5 player -->
				<video
					v-if="imageViewMode == ImageViewMode.Video"
					width="auto"
					height="auto"
					id="image"
					ref="videoElement"
					controls
					class="absolute m-auto w-auto h-auto"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					autobuffer
					:autoplay="lycheeStore.can_autoplay"
				>
					<source :src="props.photo.size_variants.original?.url ?? ''" />
					Your browser does not support the video tag.
				</video>
				<!-- This is a raw file: put a place holder -->
				<img
					v-if="imageViewMode == ImageViewMode.Raw"
					id="image"
					alt="placeholder"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:src="getPlaceholderIcon()"
				/>
				<!-- This is a normal image: medium or original -->
				<img
					v-if="imageViewMode == ImageViewMode.Medium"
					id="image"
					alt="medium"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:src="props.photo.size_variants.medium?.url ?? ''"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:srcset="srcSetMedium"
				/>
				<img
					v-if="imageViewMode == ImageViewMode.Original"
					id="image"
					alt="big"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:style="style"
					:src="props.photo.size_variants.original?.url ?? ''"
				/>
				<!-- This is a livephoto : medium -->
				<div
					v-if="imageViewMode == ImageViewMode.LivePhotoMedium"
					id="livephoto"
					data-live-photo
					data-proactively-loads-video="true"
					:data-photo-src="photo?.size_variants.medium?.url"
					:data-video-src="photo?.live_photo_url"
					class="absolute m-auto w-auto h-auto"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:style="style"
				></div>
				<!-- This is a livephoto : full -->
				<div
					v-if="imageViewMode == ImageViewMode.LivePhotoOriginal"
					id="livephoto"
					data-live-photo
					data-proactively-loads-video="true"
					:data-photo-src="photo?.size_variants.original?.url"
					:data-video-src="photo?.live_photo_url"
					class="absolute m-auto w-auto h-auto"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:style="style"
				></div>

				<!-- <x-gallery.photo.overlay /> -->
			</div>
			<NextPrevious
				v-if="photo.previous_photo_id !== null && !is_slideshow_active"
				:albumId="props.albumId"
				:photoId="photo.previous_photo_id"
				:is_next="false"
				:style="previousStyle"
			/>
			<NextPrevious
				v-if="photo.next_photo_id !== null && !is_slideshow_active"
				:albumId="props.albumId"
				:photoId="photo.next_photo_id"
				:is_next="true"
				:style="nextStyle"
			/>
			<Overlay :photo="photo" v-if="!is_exif_disabled" />
			<Dock
				v-if="photo.rights.can_edit && !is_photo_edit_open"
				:photo="photo"
				@toggleStar="emits('toggleStar')"
				@setAlbumHeader="emits('setAlbumHeader')"
				@rotatePhotoCCW="emits('rotatePhotoCCW')"
				@rotatePhotoCW="emits('rotatePhotoCW')"
				@toggleMove="emits('toggleMove')"
				@toggleDelete="emits('toggleDelete')"
			/>
		</div>
		<PhotoDetails v-model:are-details-open="are_details_open" :photo="photo" :is-map-visible="props.isMapVisible" v-if="!is_exif_disabled" />
	</div>
</template>
<script setup lang="ts">
import { ImageViewMode, usePhotoBaseFunction } from "@/composables/photo/basePhoto";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useImageHelpers } from "@/utils/Helpers";
import { storeToRefs } from "pinia";
import { onMounted, ref } from "vue";
import NextPrevious from "./NextPrevious.vue";
import Overlay from "./Overlay.vue";
import PhotoDetails from "@/components/drawers/PhotoDetails.vue";
import PhotoHeader from "@/components/headers/PhotoHeader.vue";
import Dock from "./Dock.vue";
import { watch } from "vue";
import { useSwipe, type UseSwipeDirection } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onUnmounted } from "vue";
import { useDebounceFn } from "@vueuse/core";

const swipe = ref<HTMLElement | null>(null);
const videoElement = ref<HTMLVideoElement | null>(null);

const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();

const { is_exif_disabled } = storeToRefs(lycheeStore);
const { is_photo_edit_open, is_slideshow_active, are_details_open, is_full_screen } = storeToRefs(togglableStore);

const props = defineProps<{
	albumId: string;
	photo: App.Http.Resources.Models.PhotoResource;
	photos: App.Http.Resources.Models.PhotoResource[];
	isMapVisible: boolean;
}>();

const photo = ref(props.photo);
const photos = ref(props.photos);

const emits = defineEmits<{
	toggleStar: [];
	setAlbumHeader: [];
	rotatePhotoCCW: [];
	rotatePhotoCW: [];
	toggleMove: [];
	toggleDelete: [];
	updated: [];
	rotateOverlay: [];
	toggleSlideShow: [];
	goBack: [];
	next: [];
	previous: [];
}>();

const { previousStyle, nextStyle, srcSetMedium, style, imageViewMode } = usePhotoBaseFunction(photo, photos, videoElement);
const { getPlaceholderIcon } = useImageHelpers();

// We use debounce to avoid multiple skipping too many pictures in one go via the trackpad
function scrollTo(event: WheelEvent) {
	if (shouldIgnoreKeystroke()) {
		return;
	}

	if (is_photo_edit_open.value) {
		// We do nothing! Otherwise we are switching photos without noticing.
		// especially with trackpads.
		return;
	}

	const delta = Math.sign(event.deltaY);
	if (delta > 0) {
		emits("next");
	} else if (delta < 0) {
		emits("previous");
	}
}
const debouncedScrollTo = useDebounceFn(scrollTo, 10);

onMounted(() => {
	window.addEventListener("wheel", debouncedScrollTo);
});

onUnmounted(() => {
	window.removeEventListener("wheel", debouncedScrollTo);
});

useSwipe(swipe, {
	onSwipe(_e: TouchEvent) {},
	onSwipeEnd(_e: TouchEvent, direction: UseSwipeDirection) {
		if (direction === "left") {
			emits("next");
		} else if (direction === "right") {
			emits("previous");
		} else {
			emits("goBack");
		}
	},
});

watch(
	() => props.photo.id,
	() => {
		photo.value = props.photo;
	},
);
</script>
