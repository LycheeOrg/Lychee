<template>
	<LoadingProgress v-model:loading="isInitialLoading" />
	<!--
		Feature 068: no `h-svh overflow-y-auto` wrapper - the virtualizer here is
		`useWindowVirtualizer`, which tracks the real `window`'s scroll position
		(same reasoning as Timeline.vue's own identical comment on this exact
		pitfall: a nested scrolling div desyncs the virtualizer's visibility calc
		from its own render offset). All albums are already loaded in one
		unpaginated request (`loadV3()`); only DOM rendering is windowed, and each
		card's own photo preview is fetched lazily the moment that card is
		actually instantiated.
	-->
	<div>
		<UHeader :toggle="false" class="mb-8" :ui="{ root: 'bg-transparent border-b-0', center: 'flex' }">
			<template #left>
				<OpenLeftMenu />
			</template>
			<span class="absolute top-0 left-1/2 -translate-x-1/2 h-14 text-lg font-semibold hidden md:flex items-center">{{ title }}</span>
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
import AlbumCardV3 from "@/v8/components/gallery/flowModule/AlbumCardV3.vue";
import LigtBox from "@/v8/components/gallery/flowModule/LigtBox.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import ScrollTop from "@/v8/components/ScrollTop.vue";
import FlowService from "@/services/flow-service";
import PhotoChildrenV3Service from "@/services/photo-children-v3-service";
import { adaptPhotoTile, type AdaptedPhotoTile } from "@/v8/utils/adaptPhotoTile";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { useFlowStateStore } from "@/stores/FlowState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useElementBounding } from "@vueuse/core";
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

// Only true until the album list arrives, for the full-screen `LoadingProgress` overlay.
const isInitialLoading = ref(true);
const config = ref<App.Http.Resources.Flow.InitResource | undefined>(undefined);

// Feature 068: the selected album only ever has the lazily-loaded, capped
// preview for its id.
const selectedAlbumIdV3 = ref<string | undefined>(undefined);

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
			openSelectionV3(albumId, adaptPhotoTile(0, ratios, albumId, lycheeStore));
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
	selectPhotoV3(albumId, photo);
}

/**
 * Sets the lightbox's current v3 photo and prefetches tier-3 `details` (G5)
 * for it and its immediate neighbors - a `cardPhotosV3` tile is only ever
 * `ratios`-derived (size_variants/description/EXIF/etc. all placeholders)
 * until this resolves, mutating `photo` (and any found neighbors) in place,
 * which `photoStore.photo` already references. Called on every v3 photo
 * change (`openSelectionV3()`, `next()`, `previous()`), not just the
 * initial open - otherwise only the first photo's immediate neighbors ever
 * get resolved, and hopping further (e.g. next twice) lands on a tile whose
 * placeholders were never filled in. Mirrors `PhotoState.ts`'s own `load()`,
 * which re-runs this same neighbor prefetch on every `photoId` change for
 * the same reason.
 */
function selectPhotoV3(albumId: string, photo: AdaptedPhotoTile) {
	photoStore.photo = photo;

	const cardPhotos = flowState.cardPhotosV3[albumId];
	const neighborIds = [photo.previous_photo_id, photo.next_photo_id].filter((id): id is string => id !== null && id !== undefined);
	const neighborTiles = cardPhotos?.filter((p) => neighborIds.includes(p.id)) ?? [];
	void flowState.loadPhotoDetailsV3(albumId, [photo, ...neighborTiles]);
}

function loadV3() {
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
			isInitialLoading.value = false;
		});
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

	return loadV3();
});

onUnmounted(() => {
	flowState.resetV3();
});

function goBack() {
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

	const albumId = selectedAlbumIdV3.value;
	const photo = albumId !== undefined ? flowState.cardPhotosV3[albumId]?.find((photo) => photo.id === photoStore.photo?.next_photo_id) : undefined;
	if (albumId !== undefined && photo !== undefined) {
		selectPhotoV3(albumId, photo);
	}
}

function previous() {
	if (!photoStore.hasPrevious) {
		return;
	}

	const albumId = selectedAlbumIdV3.value;
	const photo =
		albumId !== undefined ? flowState.cardPhotosV3[albumId]?.find((photo) => photo.id === photoStore.photo?.previous_photo_id) : undefined;
	if (albumId !== undefined && photo !== undefined) {
		selectPhotoV3(albumId, photo);
	}
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
