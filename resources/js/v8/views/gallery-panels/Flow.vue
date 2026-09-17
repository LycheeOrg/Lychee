<template>
	<LoadingProgress v-model:loading="isInitialLoading" />
	<!-- v2 path (flag off): unchanged, own `h-svh overflow-y-auto` scroll container. -->
	<div v-if="!flowState.isFlowSoaActive" class="h-svh overflow-y-auto">
		<UHeader :toggle="false" class="mb-8" :ui="{ root: 'bg-transparent border-b-0', center: 'flex' }">
			<template #left>
				<OpenLeftMenu />
			</template>

			<span class="absolute left-1/2 -translate-x-1/2 text-lg font-semibold text-center hidden md:block">{{ title }}</span>
		</UHeader>
		<div class="absolute top-0 left-1/2 text-center text-lg font-semibold text-white w-xs -translate-x-1/2 md:hidden">
			{{ title }}
		</div>
		<div v-if="config !== undefined" class="flex flex-col items-center gap-16 mb-16 px-8">
			<TransitionGroup name="slide-fade">
				<AlbumCard v-for="album in albums" :key="`album-${album.id}`" :album="album" :config="config" @set-selection="setSelection" />
			</TransitionGroup>
			<div v-if="albums && albums.length === 0" class="h-[70vh] text-muted flex items-center">{{ $t("flow.no_content") }}</div>
			<div v-if="currentPage < lastPage" ref="sentinel" class="sentinel"></div>
		</div>
		<LigtBox @go-back="goBack" @next="next" @previous="previous" />
		<div v-if="isLoading && !isTouchDevice()" class="flex justify-center">
			<LycheeLoadingIcon fast class="text-2xl" />
		</div>
		<GalleryFooter v-once />
		<ScrollTop v-if="photoStore.isLoaded" target="parent" />
	</div>

	<!--
		v3 path (Feature 068, flag on): no `h-svh overflow-y-auto` wrapper - the
		virtualizer here is `useWindowVirtualizer`, which tracks the real
		`window`'s scroll position (same reasoning as Timeline.vue's own
		identical comment on this exact pitfall: a nested scrolling div
		desyncs the virtualizer's visibility calc from its own render offset).
		All albums are already loaded in one unpaginated request (`loadV3()`);
		only DOM rendering is windowed, and each card's own photo preview is
		fetched lazily the moment that card is actually instantiated.
	-->
	<div v-else>
		<UHeader :toggle="false" class="mb-8" :ui="{ root: 'bg-transparent border-b-0', center: 'flex' }">
			<template #left>
				<OpenLeftMenu />
			</template>
			<span class="absolute left-1/2 -translate-x-1/2 text-lg font-semibold text-center hidden md:block">{{ title }}</span>
		</UHeader>
		<div v-if="config !== undefined" class="px-8 mb-16">
			<div v-if="flowState.flowV3.length === 0 && flowState.flowV3Loaded" class="h-[70vh] text-muted flex items-center justify-center">
				{{ $t("flow.no_content") }}
			</div>
			<div v-else ref="containerRefV3" class="relative w-full" :style="{ height: `${totalSizeV3}px` }">
				<div
					v-for="item in virtualItemsV3"
					:key="String(item.key)"
					:ref="(el) => virtualizerV3.measureElement(el as Element)"
					:data-index="item.index"
					class="absolute top-0 left-0 w-full flex justify-center pb-16"
					:style="{ transform: `translateY(${item.start - scrollMarginV3}px)` }"
				>
					<AlbumCardV3
						v-if="flowState.flowV3[item.index]"
						:album="flowState.flowV3[item.index]"
						:config="config"
						@set-selection="setSelectionV3"
					/>
				</div>
			</div>
		</div>
		<LigtBox @go-back="goBack" @next="next" @previous="previous" />
		<GalleryFooter v-once />
		<ScrollTop v-if="photoStore.isLoaded" />
	</div>
</template>
<script setup lang="ts">
import GalleryFooter from "@/v8/components/footers/GalleryFooter.vue";
import AlbumCard from "@/v8/components/gallery/flowModule/AlbumCard.vue";
import AlbumCardV3 from "@/v8/components/gallery/flowModule/AlbumCardV3.vue";
import LigtBox from "@/v8/components/gallery/flowModule/LigtBox.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import ScrollTop from "@/v8/components/ScrollTop.vue";
import FlowService from "@/services/flow-service";
import PhotoChildrenV3Service from "@/services/photo-children-v3-service";
import { adaptPhotoTile, type AdaptedPhotoTile } from "@/v8/utils/adaptPhotoTile";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { useFlowStateStore } from "@/stores/FlowState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useElementBounding, useIntersectionObserver } from "@vueuse/core";
import { useWindowVirtualizer } from "@tanstack/vue-virtual";
import { storeToRefs } from "pinia";
import { computed, onMounted } from "vue";
import { onUnmounted } from "vue";
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useLtRorRtL } from "@/utils/Helpers";
import { useUserStore } from "@/stores/UserState";
import { usePhotoStore } from "@/stores/PhotoState";
import { definePanelShortcuts } from "@/v8/composables/usePanelShortcuts";

const { isLTR } = useLtRorRtL();

const userStore = useUserStore();
const photoStore = usePhotoStore();
const toast = useAppToast();
const lycheeStore = useLycheeStateStore();
const flowState = useFlowStateStore();
const router = useRouter();

const leftMenuStore = useLeftMenuStateStore();
const { title, image_overlay_type } = storeToRefs(lycheeStore);
const { are_nsfw_blurred, are_nsfw_consented } = storeToRefs(flowState);

const isLoading = ref(true);
// Only true until the first page of albums arrives - stays false afterwards so infinite-scroll
// pagination (via `load()` below) never re-triggers the full-screen `LoadingProgress` overlay.
const isInitialLoading = ref(true);
const albums = ref<App.Http.Resources.Flow.FlowItemResource[] | undefined>(undefined);
const config = ref<App.Http.Resources.Flow.InitResource | undefined>(undefined);
const currentPage = ref(1);
const lastPage = ref(0);
const sentinel = ref(null);
let stopObserver = null;

const selectedAlbum = ref<App.Http.Resources.Flow.FlowItemResource | undefined>(undefined);
// Feature 068: v3's own selection state - `selectedAlbum` above stays
// exclusively v2 (a full FlowItemResource with nested photos); v3 only ever
// has the lazily-loaded, capped preview for whichever album id is selected.
const selectedAlbumIdV3 = ref<string | undefined>(undefined);

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

function setSelectionV3(albumId: string, photoId: string) {
	if (config.value === undefined) {
		console.error("Config is not defined, cannot set selection.");
		return;
	}

	const photos = flowState.cardPhotosV3[albumId];
	const photo = photos?.find((p) => p.id === photoId);
	if (photo !== undefined) {
		openSelectionV3(albumId, photo);
		return;
	}

	// The clicked photo (e.g. an explicit cover) may fall outside the
	// card's already-loaded, capped preview - fetch it directly rather
	// than silently opening nothing or the wrong photo.
	const generation = flowState.generationV3;
	PhotoChildrenV3Service.getRatios(albumId, { photoIds: [photoId] })
		.then((response) => {
			// resetV3() (e.g. this component unmounting) may have run while
			// this request was in flight - discard a now-obsolete response
			// rather than routing to/selecting a photo from a torn-down view.
			if (generation !== flowState.generationV3) {
				return;
			}
			const ratios = response.data;
			if (ratios.ids.length === 0) {
				return;
			}
			openSelectionV3(albumId, adaptPhotoTile(0, ratios, albumId));
		})
		.catch((e) => {
			if (generation !== flowState.generationV3) {
				return;
			}
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function openSelectionV3(albumId: string, photo: AdaptedPhotoTile) {
	if (config.value === undefined) {
		return;
	}

	if (config.value.is_open_album_on_click) {
		router.push({ name: "flow-album", params: { albumId, photoId: photo.id } });
		return;
	}

	selectedAlbumIdV3.value = albumId;
	photoStore.photo = photo;
}

function load() {
	isLoading.value = true;
	FlowService.get(currentPage.value).then((data) => {
		isLoading.value = false;
		isInitialLoading.value = false;
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

function loadV3() {
	isLoading.value = true;
	return flowState
		.loadV3()
		.then((status) => {
			if (status === "loaded" && flowState.flowV3.length === 0) {
				router.push({ name: "login" });
			}
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			isLoading.value = false;
			isInitialLoading.value = false;
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

// Feature 068: dynamically-measured (not analytic/uniform) virtualization -
// card height depends on variable text/photo content, not a WASM-packed
// layout. `useWindowVirtualizer` mirrors this codebase's own established
// precedent (AlbumListViewVirtual.vue/PhotoGridVirtual.vue/Timeline.vue),
// `measureElement` mirrors `@tanstack/virtual-core`'s own documented
// dynamic-sizing pattern (ResizeObserver-driven remeasurement once bound).
const containerRefV3 = ref<HTMLElement>();
const { top: viewportTopV3 } = useElementBounding(containerRefV3);
const scrollMarginV3 = computed(() => viewportTopV3.value + window.scrollY);

const virtualizerV3 = useWindowVirtualizer(
	computed(() => ({
		count: flowState.flowV3.length,
		// Rough initial guess only - measureElement() corrects it to the real,
		// rendered height once each card mounts (skeleton or real content).
		estimateSize: () => 800,
		overscan: 3,
		getItemKey: (index: number) => flowState.flowV3[index]?.id ?? index,
		scrollMargin: scrollMarginV3.value,
	})),
);

const totalSizeV3 = computed(() => virtualizerV3.value.getTotalSize());
const virtualItemsV3 = computed(() => virtualizerV3.value.getVirtualItems());

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

	if (flowState.isFlowSoaActive) {
		return loadV3();
	}

	load();
});

stopObserver = registerSentinel();

onUnmounted(() => {
	stopObserver();
	flowState.resetV3();
});

function goBack() {
	selectedAlbum.value = undefined;
	selectedAlbumIdV3.value = undefined;
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

	if (flowState.isFlowSoaActive) {
		const photos = selectedAlbumIdV3.value !== undefined ? flowState.cardPhotosV3[selectedAlbumIdV3.value] : undefined;
		photoStore.photo = photos?.find((photo) => photo.id === photoStore.photo?.next_photo_id);
		return;
	}

	photoStore.photo = selectedAlbum.value?.photos.find((photo) => photo.id === photoStore.photo?.next_photo_id);
}

function previous() {
	if (!photoStore.hasPrevious) {
		return;
	}

	if (flowState.isFlowSoaActive) {
		const photos = selectedAlbumIdV3.value !== undefined ? flowState.cardPhotosV3[selectedAlbumIdV3.value] : undefined;
		photoStore.photo = photos?.find((photo) => photo.id === photoStore.photo?.previous_photo_id);
		return;
	}

	photoStore.photo = selectedAlbum.value?.photos.find((photo) => photo.id === photoStore.photo?.previous_photo_id);
}

definePanelShortcuts({
	arrowleft: () => photoStore.isLoaded && (isLTR() ? photoStore.hasPrevious && previous() : photoStore.hasNext && next()),
	arrowright: () => photoStore.isLoaded && (isLTR() ? photoStore.hasNext && next() : photoStore.hasPrevious && previous()),
	o: () => photoStore.isLoaded && rotateOverlay(),
	escape: {
		usingInput: true,
		handler: () => {
			// 1. lose focus
			if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
				document.activeElement.blur();
				return;
			}

			goBack();
		},
	},
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
