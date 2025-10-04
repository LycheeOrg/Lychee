<template>
	<LoadingProgress v-model:loading="isLoading" />
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 bg-transparent mb-8 rounded-none">
			<template #start>
				<OpenLeftMenu />
			</template>

			<template #center>
				<span class="text-lg font-semibold text-center hidden md:block">{{ title }}</span>
			</template>

			<template #end> </template>
		</Toolbar>
		<div class="absolute top-0 left-1/2 text-center text-lg font-semibold text-surface-0 w-xs -translate-x-1/2 md:hidden">
			{{ title }}
		</div>
		<div v-if="config !== undefined" class="flex flex-col items-center gap-16 mb-16 px-8">
			<TransitionGroup name="slide-fade">
				<AlbumCard v-for="album in albums" :key="`album-${album.id}`" :album="album" :config="config" @set-selection="setSelection" />
			</TransitionGroup>
			<div v-if="albums && albums.length === 0" class="h-[70vh] text-muted-color flex items-center">{{ $t("flow.no_content") }}</div>
			<div v-if="currentPage < lastPage" ref="sentinel" class="sentinel"></div>
		</div>
		<LigtBox @go-back="goBack" @next="next" @previous="previous" />
		<ProgressSpinner v-if="isLoading && !isTouchDevice()" class="flex justify-center" />
		<GalleryFooter v-once />
		<ScrollTop v-if="photoStore.isLoaded" target="parent" />
	</div>
</template>
<script setup lang="ts">
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import AlbumCard from "@/components/gallery/flowModule/AlbumCard.vue";
import LigtBox from "@/components/gallery/flowModule/LigtBox.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import FlowService from "@/services/flow-service";
import { useFlowStateStore } from "@/stores/FlowState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onKeyStroke, useIntersectionObserver } from "@vueuse/core";
import { storeToRefs } from "pinia";
import ProgressSpinner from "primevue/progressspinner";
import ScrollTop from "primevue/scrolltop";
import Toolbar from "primevue/toolbar";
import { onMounted } from "vue";
import { onUnmounted } from "vue";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useLtRorRtL } from "@/utils/Helpers";
import { useUserStore } from "@/stores/UserState";
import { usePhotoStore } from "@/stores/PhotoState";

const { isLTR } = useLtRorRtL();

const userStore = useUserStore();
const photoStore = usePhotoStore();
const lycheeStore = useLycheeStateStore();
const flowState = useFlowStateStore();
const router = useRouter();

const leftMenuStore = useLeftMenuStateStore();
const { title, image_overlay_type } = storeToRefs(lycheeStore);
const { are_nsfw_blurred, are_nsfw_consented } = storeToRefs(flowState);

const isLoading = ref(true);
const albums = ref<App.Http.Resources.Flow.FlowItemResource[] | undefined>(undefined);
const config = ref<App.Http.Resources.Flow.InitResource | undefined>(undefined);
const currentPage = ref(1);
const lastPage = ref(0);
const sentinel = ref(null);
let stopObserver = null;

const selectedAlbum = ref<App.Http.Resources.Flow.FlowItemResource | undefined>(undefined);

function setSelection(album: App.Http.Resources.Flow.FlowItemResource, idxPhoto: number) {
	if (config.value === undefined) {
		console.error("Config is not defined, cannot set selection.");
		return;
	}

	if (config.value.is_open_album_on_click) {
		router.push({ name: "flow-album", params: { albumId: album.id, photoId: album.photos[idxPhoto].id } });
		return;
	}

	selectedAlbum.value = album;
	photoStore.photo = album.photos[idxPhoto];
}

function load() {
	isLoading.value = true;
	FlowService.get(currentPage.value).then((data) => {
		isLoading.value = false;
		if (albums.value === undefined) {
			albums.value = [];
		}
		albums.value.push(...data.data.albums);
		currentPage.value = data.data.current_page;
		lastPage.value = data.data.last_page;

		if (albums.value.length === 0) {
			router.push({ name: "login" });
		}
	});
}

function registerSentinel() {
	const { stop } = useIntersectionObserver(sentinel, ([{ isIntersecting }]) => {
		if (isIntersecting && !isLoading.value && config.value !== undefined) {
			if (currentPage.value < lastPage.value) {
				currentPage.value++;
				load();
			}
		}
	});

	return stop;
}

onMounted(async () => {
	are_nsfw_consented.value = false;

	leftMenuStore.left_menu_open = false;
	await userStore.load();
	lycheeStore.load();

	await FlowService.init().then((response) => {
		config.value = response.data;
		are_nsfw_blurred.value = response.data.is_blur_nsfw_enabled;
	});

	if (userStore.isGuest && !config.value?.is_mod_flow_enabled) {
		router.push({ name: "gallery" });
		return;
	}

	load();
});

stopObserver = registerSentinel();

onUnmounted(() => stopObserver());

function goBack() {
	selectedAlbum.value = undefined;
	photoStore.reset();
}

function rotateOverlay() {
	const overlays = ["none", "desc", "date", "exif"] as App.Enum.ImageOverlayType[];
	for (let i = 0; i < overlays.length; i++) {
		if (image_overlay_type.value === overlays[i]) {
			image_overlay_type.value = overlays[(i + 1) % overlays.length];
			return;
		}
	}
}

function next() {
	if (!photoStore.hasNext) {
		return;
	}

	photoStore.photo = selectedAlbum.value?.photos.find((photo) => photo.id === photoStore.photo?.next_photo_id);
}

function previous() {
	if (!photoStore.hasPrevious) {
		return;
	}

	photoStore.photo = selectedAlbum.value?.photos.find((photo) => photo.id === photoStore.photo?.previous_photo_id);
}

onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasPrevious && previous());
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasNext && next());
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasNext && next());
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasPrevious && previous());
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && rotateOverlay());
onKeyStroke("Escape", () => {
	// 1. lose focus
	if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
		return;
	}

	goBack();
});
</script>
<style>
/*
  Enter and leave animations can use different
  durations and timing functions.
*/
.slide-fade-enter-active,
.slide-fade-leave-active {
	transition: all 0.3s ease-out;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
	transform: translateY(20px);
	opacity: 0;
}
</style>
