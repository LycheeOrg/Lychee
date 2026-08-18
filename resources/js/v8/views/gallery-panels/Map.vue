<template>
	<LoadingProgress v-model:loading="isLoading" />
	<UHeader
		:toggle="false"
		:class="{
			'max-h-14': !is_full_screen,
			'max-h-0': is_full_screen,
		}"
	>
		<template #left>
			<GoBack @go-back="goBack" />
		</template>

		{{ $t(lycheeStore.title) }}
	</UHeader>
	<div
		id="lychee_map_container"
		class="leaflet-container leaflet-touch leaflet-retina leaflet-fade-anim leaflet-grab leaflet-touch-drag leaflet-touch-zoom w-full"
		:class="is_full_screen ? 'h-svh' : 'h-[calc(100vh-3.5rem)]'"
		tabindex="0"
		style=""
	></div>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import { ref, Ref } from "vue";
import { useRouter } from "vue-router";
import L from "leaflet";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet.markercluster/dist/leaflet.markercluster.js";
import "leaflet/dist/leaflet.css";
import "leaflet-gpx/gpx.js";
import { useAppToast } from "@/v8/composables/useAppToast";
import Constants from "@/services/constants";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { onMounted } from "vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import GoBack from "@/v8/components/headers/GoBack.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import { clusterFunc } from "@/composables/photo";

type MapPhotoEntry = {
	lat?: number | null;
	lng?: number | null;
	thumbnail?: string | null;
	thumbnail2x?: string | null;
	url: string;
	url2x: string | null;
	name: string;
	taken_at: string | null;
	albumID: string | null;
	photoID: string;
};

type MapClickEvent = {
	layer: {
		photo: MapPhotoEntry;
		bindPopup: (template: string, options: { minWidth: number }) => MapClickEvent;
	};
	openPopup: () => void;
};

const props = defineProps<{
	albumId?: string;
}>();

const toast = useAppToast();
const router = useRouter();
const isLoading = ref(true);
const leftMenuStore = useLeftMenuStateStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.load();

function goBack() {
	if (props.albumId !== undefined && props.albumId !== "") {
		router.push({ name: "album", params: { albumId: props.albumId } });
	} else {
		router.push({ name: "gallery" });
	}
}
const { is_full_screen } = storeToRefs(togglableStore);

// Map stuff.
const camera_date = trans("gallery.camera_date");
const map_provider = ref<App.Http.Resources.GalleryConfigs.MapProviderData | undefined>(undefined);
const map = ref(undefined) as Ref<L.Map | undefined>;
const bounds = ref<L.LatLngBoundsExpression | undefined>(undefined);
const photoLayer = ref<unknown>(undefined);
// One entry per track (FR-055-09 UI half); keyed by track id so a future re-fetch could diff them.
const trackLayers = ref<Map<number, L.Layer>>(new Map());
const data = ref<App.Http.Resources.Collections.PositionDataResource | undefined>(undefined);

// Fixed palette cycled across tracks (Q-055-02: no persisted/user-chosen colors).
const TRACK_COLORS = ["#e6194b", "#3cb44b", "#4363d8", "#f58231", "#911eb4", "#42d4f4", "#f032e6", "#bfef45"];

function colorForTrackIndex(index: number): string {
	return TRACK_COLORS[index % TRACK_COLORS.length];
}

// Leaflet's layers control assigns overlay names via innerHTML, and track names
// are user-supplied (RenameAlbumTrackRequest only validates type/length). Encode
// here so the name renders as text instead of being parsed as HTML.
function escapeHtml(text: string): string {
	const div = document.createElement("div");
	div.textContent = text;
	return div.innerHTML;
}

function loadMapProvider() {
	AlbumService.getMapProvider()
		.then((data) => {
			map_provider.value = data.data;
			mapInit();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
			isLoading.value = false;
		});
}

function mapInit() {
	// @ts-expect-error We don't care about the default icon.
	delete L.Icon.Default.prototype._getIconUrl;
	L.Icon.Default.mergeOptions({
		iconRetinaUrl: Constants.BASE_URL + "/img/marker-icon-2x.png",
		iconUrl: Constants.BASE_URL + "/img/marker-icon.png",
		shadowUrl: Constants.BASE_URL + "/img/marker-shadow.png",
	});

	if (map_provider.value !== undefined) {
		// Set initial view to (0,0)
		map.value = L.map("lychee_map_container").setView([0.0, 0.0], 2);

		L.tileLayer(map_provider.value?.layer, { attribution: map_provider.value?.attribution }).addTo(map.value);

		open();

		fetchData();
	}
}

function fetchData() {
	AlbumService.getMapData(props.albumId)
		.then((mapData) => {
			data.value = mapData.data;
			addContentsToMap();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			isLoading.value = false;
		});
}

function open() {
	// Define how the photos on the map should look
	// @ts-expect-error Leaflet.Photo is not typed
	photoLayer.value = clusterFunc().on("click", function (e: MapClickEvent) {
		const photo: MapPhotoEntry = {
			photoID: e.layer.photo.photoID,
			albumID: e.layer.photo.albumID,
			name: e.layer.photo.name,
			url: e.layer.photo.url,
			url2x: e.layer.photo.url2x,
			taken_at: e.layer.photo.taken_at,
		};
		let template = "";

		// Retina version if available
		if (photo.url2x !== "") {
			template = template.concat(
				'<img class=" w-full h-auto" src="{url}" srcset="{url} 1x, {url2x} 2x" data-album-id="{albumID}" data-photo-id="{photoID}"/>',
				'<div class=" pointer-events-none absolute w-full bottom-0 m-0 bg-gradient-to-t from-black/40 text-shadow" style="width:401px; bottom: 13px;">',
				'<h1 class=" min-h-[19px] mt-3 mb-1 ml-3 text-white text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">{name}</h1>',
				'<p class="block mt-0 mr-0 mb-2 ml-3 text-xs text-white/70">',
				'<span class="inline-block mx-2" title="' + camera_date + '">',
				'<svg class="inline-block h-3 w-3 fill-neutral-400"><use xlink:href="#camera-slr" /></svg>',
				"</span>",
				"{taken_at}</p>",
				"</div>",
			);
		} else {
			template = template.concat(
				'<img class=" w-full h-auto" src="{url}" data-album-id="{albumID}" data-photo-id="{photoID}"/>',
				'<div class=" pointer-events-none absolute w-full bottom-0 m-0 bg-gradient-to-t from-black/40 text-shadow" style="width:401px; bottom: 13px;">',
				'<h1 class=" min-h-[19px] mt-3 mb-1 ml-3 text-white text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">{name}</h1>',
				'<p class="block mt-0 mr-0 mb-2 ml-3 text-xs text-white/70">',
				'<span class="inline-block mx-2" title="' + camera_date + '">',
				'<svg class="inline-block h-3 w-3 fill-neutral-400"><use xlink:href="#camera-slr" /></svg>',
				"</span>",
				"{taken_at}</p>",
				"</div>",
			);
		}

		e.layer
			.bindPopup(L.Util.template(template, photo), {
				minWidth: 400,
			})
			.openPopup();
	});
}

/**
 * Adds photos to the map.
 */
function addContentsToMap() {
	// check if empty
	if (data.value === undefined) return;
	if (data.value.photos.length === 0 && data.value.tracks.length === 0) return;

	// Check initializations
	if (map.value === undefined) return;
	if (photoLayer.value === null || photoLayer.value === undefined) return;

	const photos: MapPhotoEntry[] = [];
	let min_lat: number | null = null;
	let min_lng: number | null = null;
	let max_lat: number | null = null;
	let max_lng: number | null = null;

	data.value.photos.forEach(function (element: App.Http.Resources.Models.PhotoResource) {
		if (element.precomputed.latitude || element.precomputed.longitude) {
			photos.push({
				lat: element.precomputed.latitude,
				lng: element.precomputed.longitude,
				thumbnail: element.size_variants.thumb?.url ?? "img/placeholder.png",
				thumbnail2x: element.size_variants.thumb2x?.url,
				url: element.size_variants.small?.url ?? element.size_variants.medium?.url ?? "",
				url2x: element.size_variants.small2x?.url ?? "",
				name: element.title,
				taken_at: element.preformatted.taken_at,
				albumID: element.album_id,
				photoID: element.id,
			});

			// Update min/max lat/lng
			if (element.precomputed.latitude !== null && (min_lat === null || min_lat > element.precomputed.latitude)) {
				min_lat = element.precomputed.latitude;
			}
			if (element.precomputed.longitude !== null && (min_lng === null || min_lng > element.precomputed.longitude)) {
				min_lng = element.precomputed.longitude;
			}
			if (element.precomputed.latitude !== null && (max_lat === null || max_lat < element.precomputed.latitude)) {
				max_lat = element.precomputed.latitude;
			}
			if (element.precomputed.longitude !== null && (max_lng === null || max_lng < element.precomputed.longitude)) {
				max_lng = element.precomputed.longitude;
			}
		}
	});

	// Add Photos to map
	// @ts-expect-error photoLater is created by leaflet.photo and is not typed
	photoLayer.value.add(photos).addTo(map.value);

	if (photos.length > 0 && max_lat !== null && min_lat !== null && max_lng !== null && min_lng !== null) {
		// update map bounds
		const dist_lat = max_lat - min_lat;
		const dist_lng = max_lng - min_lng;
		bounds.value = [
			[min_lat - 0.1 * dist_lat, min_lng - 0.1 * dist_lng],
			[max_lat + 0.1 * dist_lat, max_lng + 0.1 * dist_lng],
		];
	}

	// add tracks: one L.GPX layer per track, colored from a fixed palette, wired into
	// Leaflet's native layers control for the legend/visibility checkboxes (Q-055-02).
	// Placed before any early return so a track still renders on a photo-less album.
	const overlays: Record<string, L.Layer> = {};
	data.value.tracks.forEach((track, index) => {
		// @ts-expect-error L.GPX is not typed
		const layer = new L.GPX(track.url, {
			async: true,
			polyline_options: { color: colorForTrackIndex(index), weight: 4 },
			marker_options: {
				startIconUrl: null,
				endIconUrl: null,
				shadowUrl: null,
			},
		})
			.on("error", function (e: { err: string }) {
				toast.add({ severity: "error", summary: trans("gallery.map.error_gpx"), detail: e.err, life: 3000 });
			})
			.on("loaded", function (e: { target: { getBounds: () => L.LatLngBounds } }) {
				if (photos.length === 0) {
					// no photos: extend the map bounds to keep every track visible
					const loadedBounds = e.target.getBounds();
					bounds.value = bounds.value instanceof L.LatLngBounds ? bounds.value.extend(loadedBounds) : loadedBounds;
					updateZoom();
				}
			});
		layer.addTo(map.value as L.Map);
		trackLayers.value.set(track.id, layer as L.Layer);
		overlays[escapeHtml(track.name)] = layer as L.Layer;
	});
	if (Object.keys(overlays).length > 0) {
		L.control.layers(undefined, overlays).addTo(map.value);
	}

	// Update Zoom and Position
	updateZoom();
}

// Adjusts zoom and position of map to show all images
function updateZoom() {
	if (map.value === undefined) {
		return;
	}
	if (bounds.value) {
		map.value.fitBounds(bounds.value);
	} else {
		map.value.fitWorld();
	}
}

defineShortcuts({
	escape: {
		usingInput: true,
		handler: () => goBack(),
	},
});

onMounted(() => {
	leftMenuStore.left_menu_open = false;
	loadMapProvider();
});
</script>
<style lang="css">
.leaflet-marker-photo {
	border: 2px solid #fff;
	box-shadow: 3px 3px 10px #888;
}

.leaflet-marker-photo div {
	width: 100%;
	height: 100%;
	background-size: cover;
	background-position: center center;
	background-repeat: no-repeat;
}

.leaflet-marker-photo b {
	position: absolute;
	top: -7px;
	right: -11px;
	color: #555;
	background-color: #fff;
	border-radius: 8px;
	height: 12px;
	min-width: 12px;
	line-height: 12px;
	text-align: center;
	padding: 3px;
	box-shadow: 0 3px 14px rgba(0, 0, 0, 0.4);
}
</style>
