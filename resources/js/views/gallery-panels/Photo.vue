<template>
	<div
		id="shutter"
		:class="{
			'absolute w-screen h-dvh bg-surface-950 transition-opacity duration-1000 ease-in-out top-0 left-0': true,
			'z-50 opacity-0 pointer-events-none': is_slideshow_active,
		}"
	></div>
	<div class="absolute top-0 left-0 w-full flex h-full overflow-hidden bg-black" v-if="photo">
		<PhotoHeader :albumid="props.albumid" :photo="photo" @slideshow="slideshow" />
		<div class="w-0 flex-auto relative">
			<div
				id="imageview"
				class="absolute top-0 left-0 w-full h-full bg-black flex items-center justify-center overflow-hidden"
				@click="rotateOverlay()"
				ref="swipe"
			>
				<!--  This is a video file: put html5 player -->
				<video
					v-if="imageViewMode == 0"
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
					<source :src="photo?.size_variants.original?.url ?? ''" />
					Your browser does not support the video tag.
				</video>
				<!-- This is a raw file: put a place holder -->
				<img
					v-if="imageViewMode == 1"
					id="image"
					alt="placeholder"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:src="getPlaceholderIcon()"
				/>
				<!-- This is a normal image: medium or original -->
				<img
					v-if="imageViewMode == 2"
					id="image"
					alt="medium"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:src="photo.size_variants.medium?.url ?? ''"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:srcset="srcSetMedium"
				/>
				<img
					v-if="imageViewMode == 3"
					id="image"
					alt="big"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:style="style"
					:src="photo?.size_variants.original?.url ?? ''"
				/>
				<!-- This is a livephoto : medium -->
				<div
					v-if="imageViewMode == 4"
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
					v-if="imageViewMode == 5"
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
				:albumId="props.albumid"
				:photoId="photo.previous_photo_id"
				:is_next="false"
				:style="previousStyle"
			/>
			<NextPrevious
				v-if="photo.next_photo_id !== null && !is_slideshow_active"
				:albumId="props.albumid"
				:photoId="photo.next_photo_id"
				:is_next="true"
				:style="nextStyle"
			/>
			<Overlay :photo="photo" :image-overlay-type="lycheeStore.image_overlay_type" />
			<div
				v-if="photo?.rights.can_edit && !is_edit_open"
				class="absolute top-0 sm:h-1/4 w-full sm:w-1/2 left-1/2 -translate-x-1/2 opacity-50 lg:opacity-10 group lg:hover:opacity-100 transition-opacity duration-500 ease-in-out z-20 mt-14 sm:mt-0"
				:class="is_slideshow_active ? 'hidden' : ''"
			>
				<span class="absolute left-1/2 -translate-x-1/2 p-1 min-w-[25%] w-full filter-shadow text-center">
					<DockButton
						icon="star"
						:class="photo.is_starred ? 'fill-yellow-500 lg:hover:fill-yellow-100' : 'fill-white lg:hover:fill-yellow-500'"
						v-tooltip.bottom="photo.is_starred ? $t('gallery.photo.action.unstar') : $t('gallery.photo.action.star')"
						v-on:click="toggleStar()"
					/>
					<DockButton
						pi="image"
						class="lg:hover:text-primary-500 text-white"
						v-tooltip.bottom="$t('gallery.photo.action.set_album_header')"
						v-on:click="setAlbumHeader()"
					/>

					<template v-if="lycheeStore.can_rotate">
						<DockButton icon="counterclockwise" class="fill-white lg:hover:fill-primary-500" v-on:click="rotatePhotoCCW()" />
						<DockButton icon="clockwise" class="fill-white lg:hover:fill-primary-500" v-on:click="rotatePhotoCW()" />
					</template>
					<DockButton
						icon="transfer"
						class="fill-white lg:hover:fill-primary-500"
						v-tooltip.bottom="$t('gallery.photo.action.move')"
						v-on:click="toggleMove"
					/>
					<DockButton
						icon="trash"
						class="fill-red-600 lg:fill-white lg:hover:fill-red-600"
						v-tooltip.bottom="$t('gallery.photo.action.delete')"
						v-on:click="toggleDelete"
					/>
				</span>
			</div>
		</div>
		<PhotoDetails v-model:are-details-open="are_details_open" :photo="photo" :is-map-visible="album?.config.is_map_accessible ?? false" />
	</div>
	<PhotoEdit v-if="photo?.rights.can_edit" :photo="photo" v-model:visible="is_edit_open" />
	<MoveDialog :photo="photo" v-model:visible="isMoveVisible" :parent-id="props.albumid" @moved="updated" />
	<DeleteDialog :photo="photo" v-model:visible="isDeleteVisible" :parent-id="props.albumid" @deleted="updated" />
</template>
<script setup lang="ts">
import DockButton from "@/components/gallery/photo/DockButton.vue";
import NextPrevious from "@/components/gallery/photo/NextPrevious.vue";
import AlbumService from "@/services/album-service";
import PhotoDetails from "@/components/drawers/PhotoDetails.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { ref, watch, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import PhotoHeader from "@/components/headers/PhotoHeader.vue";
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import PhotoService from "@/services/photo-service";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import Overlay from "@/components/gallery/photo/Overlay.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import { storeToRefs } from "pinia";
import SearchService from "@/services/search-service";
import { usePhotoBaseFunction } from "@/composables/photo/basePhoto";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useToast } from "primevue/usetoast";
import type { UseSwipeDirection } from "@vueuse/core";
import { useSwipe } from "@vueuse/core";
import { useImageHelpers } from "@/utils/Helpers";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { trans } from "laravel-vue-i18n";

const swipe = ref<HTMLElement | null>(null);
const videoElement = ref<HTMLVideoElement | null>(null);
const props = defineProps<{
	albumid: string;
	photoid: string;
}>();

const router = useRouter();
const route = useRoute();
const toast = useToast();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_full_screen, is_edit_open, are_details_open, is_slideshow_active } = storeToRefs(togglableStore);

const { isDeleteVisible, toggleDelete, isMoveVisible, toggleMove } = useGalleryModals(togglableStore);

const photoId = ref(props.photoid);
const { photo, album, photos, previousStyle, nextStyle, srcSetMedium, style, imageViewMode, refresh, hasPrevious, hasNext } = usePhotoBaseFunction(
	photoId,
	videoElement,
);
const { getPlaceholderIcon } = useImageHelpers();

const { slideshow_timeout } = storeToRefs(lycheeStore);

function getNext() {
	router.push({ name: "photo", params: { albumid: props.albumid, photoid: photo.value?.next_photo_id ?? "" } });
}

function getPrevious() {
	router.push({ name: "photo", params: { albumid: props.albumid, photoid: photo.value?.previous_photo_id ?? "" } });
}

const { slideshow, start, next, previous, stop } = useSlideshowFunction(
	1000,
	is_slideshow_active,
	slideshow_timeout,
	videoElement,
	getNext,
	getPrevious,
);

function load() {
	if (togglableStore.isSearchActive) {
		const albumId = togglableStore.search_album_id;
		const page = togglableStore.search_page;
		const term = togglableStore.search_term;
		SearchService.search(albumId, term, page).then((response) => {
			photos.value = response.data.photos;
			refresh();
		});
		return;
	}

	// This will crash so we redirect to the gallery
	if (props.albumid === "search") {
		router.push({ name: "gallery" });
		return;
	}

	AlbumService.get(props.albumid).then((response) => {
		album.value = response.data;
		photos.value = album.value?.resource?.photos ?? [];
		refresh();
	});
}

function updated() {
	AlbumService.clearCache(props.albumid);
	goBack();
}

function goBack() {
	if (togglableStore.isSearchActive && togglableStore.search_album_id === null) {
		router.push({ name: "search" });
		return;
	}
	if (togglableStore.isSearchActive) {
		router.push({ name: "search-with-album", params: { albumid: togglableStore.search_album_id } });
		return;
	}

	router.push({ name: "album", params: { albumid: props.albumid } });
}

function toggleStar() {
	PhotoService.star([photoId.value], !photo.value!.is_starred).then(() => {
		photo.value!.is_starred = !photo.value!.is_starred;
		AlbumService.clearCache(props.albumid);
	});
}

// Untested
function rotatePhotoCCW() {
	PhotoService.rotate(photoId.value, "-1").then(() => {
		AlbumService.clearCache(props.albumid);
		load();
	});
}

// Untested
function rotatePhotoCW() {
	PhotoService.rotate(photoId.value, "1").then(() => {
		AlbumService.clearCache(props.albumid);
		load();
	});
}

function setAlbumHeader() {
	PhotoService.setAsHeader(photoId.value, props.albumid, false).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("gallery.photo.action.header_set"), life: 2000 });
		AlbumService.clearCache(props.albumid);
		refresh();
	});
}

function rotateOverlay() {
	const overlays = ["none", "desc", "date", "exif"] as App.Enum.ImageOverlayType[];
	for (let i = 0; i < overlays.length; i++) {
		if (lycheeStore.image_overlay_type === overlays[i]) {
			lycheeStore.image_overlay_type = overlays[(i + 1) % overlays.length];
			return;
		}
	}
}

onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && hasPrevious() && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && hasNext() && next(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && slideshow());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && is_slideshow_active.value && stop());

// Priviledged operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photo.value?.rights.can_edit && toggleMove());
onKeyStroke("s", () => !shouldIgnoreKeystroke() && photo.value?.rights.can_edit && toggleStar());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && album.value?.resource?.rights.can_delete && toggleDelete());

function scrollTo(event: WheelEvent) {
	if (shouldIgnoreKeystroke()) {
		return;
	}

	if (is_edit_open.value) {
		// We do nothing! Otherwise we are switching photos without noticing.
		// especially with trackpads.
		return;
	}

	if (route.name !== "photo") {
		return;
	}

	const delta = Math.sign(event.deltaY);
	if (delta > 0) {
		next(true);
	} else if (delta < 0) {
		previous(true);
	}
}

useSwipe(swipe, {
	onSwipe(_e: TouchEvent) {},
	onSwipeEnd(_e: TouchEvent, direction: UseSwipeDirection) {
		if (direction === "left") {
			next(true);
		} else if (direction === "right") {
			previous(true);
		} else {
			goBack();
		}
	},
});

onMounted(() => {
	if (is_slideshow_active.value) {
		start();
	}

	window.addEventListener("wheel", scrollTo);
	load();
});

onUnmounted(() => {
	stop();
	window.removeEventListener("wheel", scrollTo);
});

watch(
	() => route.params.photoid,
	(newId, _oldId) => {
		photoId.value = newId as string;
		togglableStore.rememberScrollThumb(photoId.value);
		refresh();
	},
);
</script>
