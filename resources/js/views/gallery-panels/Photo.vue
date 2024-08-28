<template>
	<div class="absolute top-0 left-0 w-full flex h-full overflow-hidden bg-black" v-if="photo">
		<PhotoHeader :albumid="albumid" :photo="photo" v-model:is-edit-open="isEditOpen" v-model:are-details-open="areDetailsOpen" />
		<div class="w-0 flex-auto relative">
			<div
				id="imageview"
				class="absolute top-0 left-0 w-full h-full bg-black flex items-center justify-center overflow-hidden"
				v-on:click="rotateOverlay()"
			>
				<!--  This is a video file: put html5 player -->
				<video
					v-if="imageViewMode == 0"
					width="auto"
					height="auto"
					id="image"
					controls
					class="absolute m-auto w-auto h-auto"
					x-bind:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					autobuffer
					:autoplay="album?.config.can_autoplay"
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
					:src="placeholder"
				/>
				<!-- This is a normal image: medium or original -->
				<img
					v-if="imageViewMode == 2"
					id="image"
					alt="medium"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:src="photo.size_variants.medium?.url ?? ''"
					:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:srcset="srcSetMedium"
				/>
				<img
					v-if="imageViewMode == 3"
					id="image"
					alt="big"
					class="absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat"
					:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
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
					:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
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
					:class="isFullscreen ? 'max-w-full max-h-full' : 'max-wh-full-56'"
					:style="style"
				></div>

				<!-- <x-gallery.photo.overlay /> -->
			</div>
			<NextPrevious
				v-if="photo !== undefined && photo.previous_photo_id !== null"
				:albumId="albumid"
				:photoId="photo.previous_photo_id"
				:is_next="false"
				:style="previousStyle"
			/>
			<NextPrevious
				v-if="photo !== undefined && photo.next_photo_id !== null"
				:albumId="albumid"
				:photoId="photo.next_photo_id"
				:is_next="true"
				:style="nextStyle"
			/>
			<div
				v-if="photo?.rights.can_edit && !isEditOpen"
				class="absolute top-0 h-1/4 w-full sm:w-1/2 left-1/2 -translate-x-1/2 opacity-50 lg:opacity-10 group lg:hover:opacity-100 transition-opacity duration-500 ease-in-out z-20 mt-14 sm:mt-0"
			>
				<span class="absolute left-1/2 -translate-x-1/2 p-1 min-w-[25%] w-full filter-shadow text-center">
					<DockButton
						icon="star"
						:class="photo.is_starred ? 'fill-yellow-500 lg:hover:fill-yellow-100' : 'fill-white lg:hover:fill-yellow-500'"
						v-on:click="toggleStar()"
					/>
					<template v-if="album?.config.can_rotate">
						<DockButton icon="counterclockwise" class="fill-white lg:hover:fill-primary-500" v-on:click="rotatePhotoCCW()" />
						<DockButton icon="clockwise" class="fill-white lg:hover:fill-primary-500" v-on:click="rotatePhotoCW()" />
					</template>
					<DockButton icon="transfer" class="fill-white lg:hover:fill-primary-500" v-on:click="isMoveVisible = true" />
					<DockButton icon="trash" class="fill-red-600 lg:fill-white lg:hover:fill-red-600" v-on:click="isDeleteVisible = true" />
				</span>
			</div>
		</div>
		<PhotoDetails v-model:are-details-open="areDetailsOpen" :photo="photo" />
	</div>
	<PhotoEdit v-if="photo?.rights.can_edit" :photo="photo" v-model:visible="isEditOpen" />
	<Dialog v-model:visible="isMoveVisible" pt:root:class="border-none">
		<template #container="{ closeCallback }">
			<PhotoMove :photo="photo" />
		</template>
	</Dialog>
	<Dialog v-model:visible="isDeleteVisible" pt:root:class="border-none">
		<template #container="{ closeCallback }">
			<PhotoDelete :photo="photo" />
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import DockButton from "@/components/gallery/photo/DockButton.vue";
import NextPrevious from "@/components/gallery/photo/NextPrevious.vue";
import AlbumService from "@/services/album-service";
import PhotoDetails from "@/components/drawers/PhotoDetails.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { Ref, computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import PhotoHeader from "@/components/headers/PhotoHeader.vue";
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import PhotoMove from "@/components/forms/photo/PhotoMove.vue";
import Dialog from "primevue/dialog";
import PhotoDelete from "@/components/forms/photo/PhotoDelete.vue";

const props = defineProps<{
	albumid: string;
	photoid: string;
}>();

const router = useRouter();
const route = useRoute();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();

const isMoveVisible = ref(false);
const isDeleteVisible = ref(false);
const photoId = ref(props.photoid);
const photo = ref(undefined) as Ref<App.Http.Resources.Models.PhotoResource | undefined>;
const album = ref(null) as Ref<App.Http.Resources.Models.AbstractAlbumResource | null>;
const isFullscreen = ref(lycheeStore.is_full_screen);
const isEditOpen = ref(false);
const areDetailsOpen = ref(false);

const placeholder = window.assets_url + "img/placeholder.png";

const previousStyle = computed(() => {
	const previousId = photo.value?.previous_photo_id ?? null;
	if (previousId === null) {
		return "";
	}
	const previousPhoto = album.value?.resource?.photos?.find((p) => p.id);
	if (previousPhoto === undefined) {
		return "";
	}
	return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + previousPhoto.size_variants.thumb?.url + "')";
});

const nextStyle = computed(() => {
	const nextId = photo.value?.next_photo_id ?? null;
	if (nextId === null) {
		return "";
	}
	const nextPhoto = album.value?.resource?.photos.find((p) => p.id === nextId);
	if (nextPhoto === undefined) {
		return "";
	}
	return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + nextPhoto.size_variants.thumb?.url + "')";
});

const srcSetMedium = computed(() => {
	const medium = photo.value?.size_variants.medium ?? null;
	const medium2x = photo.value?.size_variants.medium2x ?? null;
	if (medium === null || medium2x === null) {
		return "";
	}

	return `${medium.url} ${medium.width}w, ${medium2x.url} ${medium2x.width}w`;
});

const style = computed(() => {
	if (!photo.value?.precomputed.is_livephoto) {
		return "background-image: url(" + photo.value?.size_variants.small?.url + ")";
	}
	if (photo.value?.size_variants.medium !== null) {
		return "width: " + photo.value?.size_variants.medium.width + "px; height: " + photo.value?.size_variants.medium.height + "px";
	}
	if (photo.value?.size_variants.original === null) {
		return "";
	}
	return "width: " + photo.value?.size_variants.original.width + "px; height: " + photo.value?.size_variants.original.height + "px";
});

const imageViewMode = computed(() => {
	if (photo.value?.precomputed.is_video) {
		return 0;
	}
	if (photo.value?.precomputed.is_raw) {
		return 1;
	}

	if (!photo.value?.precomputed.is_livephoto) {
		if (photo.value?.size_variants.medium !== null) {
			return 2;
		}
		return 3;
	}
	if (photo.value?.size_variants.medium !== null) {
		return 4;
	}
	return 5;
});

function load() {
	AlbumService.get(props.albumid).then((response) => {
		album.value = response.data;
		refresh();
	});
}

function refresh() {
	photo.value = ((album.value?.resource?.photos ?? []) as App.Http.Resources.Models.PhotoResource[]).find(
		(p: App.Http.Resources.Models.PhotoResource) => p.id === photoId.value,
	);
}

function goBack() {
	router.push({ name: "album", params: { albumid: props.albumid } });
}

load();

function toggleStar() {
	// PhotoService.toggleStar(photoId.value).then(() => {
	// 	photo.value!.is_starred = !photo.value!.is_starred;
	// });
}

function rotatePhotoCCW() {
	// PhotoService.rotate(photoId.value, -90).then(() => {
	// 	refresh();
	// });
}

function rotatePhotoCW() {
	// PhotoService.rotate(photoId.value, 90).then(() => {
	// 	refresh();
	// });
}

function rotateOverlay() {}
// photoFlags: PhotoFlagsView;
// photo: Photo;
// // photo_id: string;
// parent_id: string;

// style: string;
// srcSetMedium: string;
// mode: number;

// refreshPhotoView: (photo: Photo) => void;

// previousStyle: () => string;
// nextStyle: () => string;
// displayMap(): void;

// imageViewMode: () => number;
// getSrcSetMedium: () => string;
// getStyle: () => string;

// this.srcSetMedium = this.getSrcSetMedium();
// this.style = this.getStyle();
// this.mode = this.imageViewMode();

// 			getStyle(): string {
// 				if (!this.photo.precomputed.is_livephoto) {
// 					return "background-image: url(" + this.photo.size_variants.small?.url + ")";
// 				}
// 				if (this.photo.size_variants.medium !== null) {
// 					return "width: " + this.photo.size_variants.medium.width + "px; height: " + this.photo.size_variants.medium.height + "px";
// 				}
// 				return "width: " + this.photo.size_variants.original.width + "px; height: " + this.photo.size_variants.original.height + "px";
// 			},

watch(
	() => route.params.photoid,
	(newId, oldId) => {
		console.log("newId", newId, "oldId", oldId);
		photoId.value = newId as string;
		refresh();
	},
);
</script>
