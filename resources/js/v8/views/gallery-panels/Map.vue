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
import { computed, onUnmounted, ref, Ref, watch } from "vue";
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
import { definePanelShortcuts } from "@/v8/composables/usePanelShortcuts";
import { useMapStore, type MapBounds } from "@/stores/MapState";
import ThumbAssetService from "@/services/thumb-asset-service";

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

// Feature 067 (I8) — flag-on dispatcher, mirrors `TimelineState.ts`'s own
// `isTimelineSoaActive` pattern. Delegates to `mapStore.isMapSoaActive` (the
// same underlying `is_struct_of_array_enabled` flag) rather than
// re-deriving it here.
const mapStore = useMapStore();
const isMapSoaActive = computed(() => mapStore.isMapSoaActive);

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
// One entry per track; keyed by track id so a future re-fetch could diff them.
const trackLayers = ref<Map<number, L.Layer>>(new Map());
const data = ref<App.Http.Resources.Collections.PositionDataResource | undefined>(undefined);

// Feature 067 (I8) — SoA path state. `aggregateMarkers` renders when the
// viewport's total photo count is over `QueryMapPhotos::MAX_VIEWPORT_PHOTOS`
// (server-decided, not mirrored client-side - see `renderAggregateMarkers()`);
// under that cap, every individual photo is fed to the same
// `clusterFunc()`/`leaflet.markercluster` machinery the v2 path already uses
// instead (Q-067-13, amended).
const aggregateMarkers = ref<L.Marker[]>([]);
// `ThumbAssetService.acquire()` releases for every currently-rendered individual photo marker.
const individualPhotoReleases = ref<(() => void)[]>([]);
// Photo ids `ensureThumbnailLoaded()` has already kicked off a fetch for, in
// the current render cycle - `ThumbAssetService.acquire()` itself dedupes
// the underlying network request for a repeat call, but each `acquire()`
// still needs its own matching `release()`; without this guard, a photo
// used by both a standalone marker and a cluster badge (or several
// clusters, across zoom levels) would leak one `release()` per redundant call.
const requestedPhotoIds = ref<Set<string>>(new Set());
// Mirrors `trackLayers` above, for the v3 (viewport-independent) track fetch.
const trackLayersV3 = ref<Map<number, L.Layer>>(new Map());

// Fixed palette cycled across tracks (no persisted/user-chosen colors).
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

		L.tileLayer(map_provider.value?.layer, {
			attribution: map_provider.value?.attribution,
			referrerPolicy: "origin",
		}).addTo(map.value);

		open();

		if (isMapSoaActive.value) {
			initSoaMap();
		} else {
			fetchData();
		}
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

// ── Feature 067 (I8): SoA (v3) rendering path ─────────────────────────
// Viewport-driven buckets/photos, decoupled track loading, aggregate-badge
// vs. individually-clustered-photo split (Q-067-13, amended). Dispatched
// into from `mapInit()` above, mirroring `Timeline.vue`'s own
// `isTimelineSoaActive`-driven dispatcher. The v2 path above
// (`fetchData()`/`addContentsToMap()`/`data`) is untouched by any of this
// (NFR-067-03).

/**
 * Leaflet doesn't wrap longitude: at low zoom (e.g. the initial `fitWorld()`
 * view), `getBounds()` can report `east`/`west` far outside [-180, 180]
 * (spanning more than one copy of the globe). The API validates both against
 * `between:-180,180` (`HasMapViewportTrait`), so raw bounds must be
 * normalized before being sent. A span of a full world or more collapses to
 * the whole-world range; otherwise `west` is wrapped into [-180, 180) and
 * `east` is re-derived from it so the original span survives - if that
 * pushes `east` past 180, wrapping it back around naturally produces
 * `west > east`, `MapViewport::snapToGrid()`'s own antimeridian-crossing
 * convention.
 */
function normalizeLongitudeBounds(west: number, east: number): { west: number; east: number } {
	const span = east - west;
	if (span >= 360) {
		return { west: -180, east: 180 };
	}

	const normalizedWest = ((((west + 180) % 360) + 360) % 360) - 180;
	let normalizedEast = normalizedWest + span;
	if (normalizedEast > 180) {
		normalizedEast -= 360;
	}

	return { west: normalizedWest, east: normalizedEast };
}

function currentBoundsAndZoom(): { bounds: MapBounds; zoom: number } | undefined {
	if (map.value === undefined) return undefined;
	const b = map.value.getBounds();
	const { west, east } = normalizeLongitudeBounds(b.getWest(), b.getEast());
	return {
		bounds: { north: b.getNorth(), south: b.getSouth(), east, west },
		zoom: map.value.getZoom(),
	};
}

function onMapViewportChanged() {
	const current = currentBoundsAndZoom();
	if (current === undefined) return;
	mapStore.requestViewport(current.bounds, current.zoom);
}

function initSoaMap() {
	if (map.value === undefined) return;

	mapStore.reset();
	mapStore.setAlbumId(props.albumId !== undefined && props.albumId !== "" ? props.albumId : null);

	map.value.on("moveend zoomend", onMapViewportChanged);

	if (mapStore.albumId !== null) {
		mapStore.loadTracks();
	}

	// Eager first load - not debounced, so the map isn't empty on mount.
	const current = currentBoundsAndZoom();
	if (current === undefined) {
		isLoading.value = false;
		return;
	}
	mapStore.fetchViewportNow(current.bounds, current.zoom).finally(() => {
		isLoading.value = false;
	});
}

function clearAggregateMarkers() {
	const leafletMap = map.value;
	if (leafletMap !== undefined) {
		aggregateMarkers.value.forEach((marker) => {
			// @ts-expect-error leaflet.markercluster's global type augmentation makes L.Map.removeLayer's Layer parameter unmatchable against a plain L.marker() return here
			leafletMap.removeLayer(marker);
		});
	}
	aggregateMarkers.value = [];
}

/**
 * A plain count-badge marker at each bucket's centroid (FR-067-20); clicking
 * it zooms the map in, it never fetches member photos. Only rendered when
 * `/Map/Photos` came back empty (FR-067-09 amended: the viewport's total
 * photo count was over `QueryMapPhotos::MAX_VIEWPORT_PHOTOS`) - otherwise
 * `renderIndividualPhotos()` owns the view and every bucket is skipped, even
 * though `bucketsV3` itself is always populated regardless of that cap.
 */
function renderAggregateMarkers() {
	if (map.value === undefined) return;
	clearAggregateMarkers();

	const buckets = mapStore.bucketsV3;
	const photos = mapStore.photosV3;
	if (buckets === undefined || (photos !== undefined && photos.ids.length > 0)) return;

	for (let i = 0; i < buckets.bucket_ids.length; i++) {
		const count = buckets.counts[i];

		const lat = buckets.centroid_latitudes[i];
		const lng = buckets.centroid_longitudes[i];
		const marker = L.marker([lat, lng], {
			icon: L.divIcon({
				html: `<div class="leaflet-marker-aggregate-badge">${count}</div>`,
				className: "leaflet-marker-aggregate",
				iconSize: [40, 40],
			}),
		});
		marker.on("click", () => {
			const nextZoom = Math.min((map.value?.getZoom() ?? 0) + 2, 24);
			map.value?.setView([lat, lng], nextZoom);
		});
		marker.addTo(map.value);
		aggregateMarkers.value.push(marker);
	}
}

function clearIndividualPhotos() {
	// @ts-expect-error photoLayer is created by leaflet.photo and is not typed
	photoLayer.value?.clear();
	individualPhotoReleases.value.forEach((release) => release());
	individualPhotoReleases.value = [];
	requestedPhotoIds.value = new Set();
}

// Sentinel distinguishing a v3 entry still awaiting its lazily-loaded
// thumbnail from a v2 entry, which already carries a real URL synchronously.
const UNRESOLVED_THUMBNAIL = "img/placeholder.png";

function buildPhotoIconHtml(thumbnail: string): string {
	return `<div style="background-image: url(${thumbnail});"></div>`;
}

/**
 * Fetches `entry`'s real thumbnail at most once per render cycle (guarded by
 * `requestedPhotoIds` - see its own comment for why), calling `onResolved`
 * once it's ready. Below `MAX_VIEWPORT_PHOTOS` (Q-067-13, amended) most
 * photos in a typical viewport end up hidden inside a cluster badge, which
 * only ever displays ONE representative child's thumbnail - eagerly
 * fetching every photo regardless (the previous version of this code) meant
 * paying for a `ThumbAssetService`/Asset-endpoint request per photo even
 * though only a handful were ever actually shown (owner: "we are loading
 * all the images even though some are clustered ... shouldn't we only load
 * the images when needed?"). Callers decide *when* a photo is actually
 * needed - see `renderIndividualPhotos()`'s marker `'add'` listener for
 * standalone/spiderfied markers, and `iconCreateFunction` in `open()` for a
 * cluster's representative child.
 */
function ensureThumbnailLoaded(entry: MapPhotoEntry, onResolved: (objectUrl: string) => void): void {
	// A photo with no resolvable containing album (S-067-09) has no
	// `{album_id}` to key the Asset endpoint on - it keeps the placeholder icon.
	if (entry.albumID === null || requestedPhotoIds.value.has(entry.photoID)) return;
	requestedPhotoIds.value.add(entry.photoID);

	const { promise, release } = ThumbAssetService.acquire(entry.albumID, entry.photoID, "small");
	individualPhotoReleases.value.push(release);
	promise
		.then((objectUrl) => {
			entry.thumbnail = objectUrl;
			entry.url = objectUrl;
			onResolved(objectUrl);
		})
		.catch(() => {
			// Leave the placeholder icon in place - mirrors <Thumb>'s own failure handling.
		});
}

/**
 * Below `MAX_VIEWPORT_PHOTOS` (Q-067-13, amended), the backend ships every
 * individual photo in the viewport with no server-side clustering at all -
 * handed straight to the same `clusterFunc()`/`Cluster.add()` machinery the
 * v2 (non-SoA) `addContentsToMap()` path already uses, so Leaflet's own
 * pixel-radius clustering (graduated sizes, real thumbnails) does the work
 * that a fixed SQL grid could only ever approximate (owner: "the clustering
 * threshold is too wide"). Every marker starts on a shared placeholder icon
 * (`MapPhotoResource` supplies no thumbnail URL at all, Q-067-12); a
 * standalone/spiderfied marker's own `'add'` event - fired by Leaflet
 * exactly when that specific marker (not a cluster standing in for it) is
 * actually placed on the map - is what triggers `ensureThumbnailLoaded()`
 * for it, so a photo hidden inside a cluster the whole time never gets
 * fetched at all.
 */
function renderIndividualPhotos() {
	if (map.value === undefined || photoLayer.value === undefined) return;
	clearIndividualPhotos();

	const photos = mapStore.photosV3;
	if (photos === undefined || photos.ids.length === 0) return;

	const entries: MapPhotoEntry[] = photos.ids.map((photoID, i) => ({
		lat: photos.latitudes[i],
		lng: photos.longitudes[i],
		thumbnail: UNRESOLVED_THUMBNAIL,
		url: "",
		url2x: "",
		name: photos.titles[i],
		taken_at: photos.taken_ats[i],
		albumID: photos.album_ids[i],
		photoID,
	}));

	// @ts-expect-error photoLayer is created by leaflet.photo and is not typed
	photoLayer.value.add(entries);

	// @ts-expect-error photoLayer is created by leaflet.photo and is not typed
	const markers = photoLayer.value.getPhotoMarkers() as (L.Marker & { photo?: MapPhotoEntry })[];
	markers.forEach((marker) => {
		const loadIfNeeded = () => {
			if (marker.photo === undefined) return;
			ensureThumbnailLoaded(marker.photo, (objectUrl) => {
				// @ts-expect-error refreshIconOptions is added by leaflet.markercluster's own L.Marker.include() and is not typed
				marker.refreshIconOptions({ html: buildPhotoIconHtml(objectUrl) }, true);
			});
		};
		// `.add()` above may already have synchronously placed a standalone
		// marker on the map (leaflet.markercluster's bulk-add path) before this
		// listener could be attached - `_map` (Leaflet's own internal "is this
		// layer currently on the map" flag) catches that case; `.once('add', ...)`
		// catches a marker that's clustered now but becomes standalone later,
		// on a future zoom.
		// @ts-expect-error `_map` is protected, but there's no public equivalent for "is this layer on the map right now"
		if (marker._map !== undefined) {
			loadIfNeeded();
		}
		marker.once("add", loadIfNeeded);
	});
}

function clearTracksV3() {
	const leafletMap = map.value;
	if (leafletMap !== undefined) {
		trackLayersV3.value.forEach((layer) => {
			// @ts-expect-error leaflet.markercluster's global type augmentation makes L.Map.removeLayer's Layer parameter unmatchable against an L.GPX layer here
			leafletMap.removeLayer(layer);
		});
	}
	trackLayersV3.value.clear();
}

/**
 * Fetched once via `MapState.ts.loadTracks()` when an album context is
 * present, independent of viewport changes (FR-067-22) — `L.GPX` layer
 * rendering, the fixed color palette, and the layers control are unchanged
 * from the v2 path's own `addContentsToMap()` track-rendering block.
 */
function renderTracksV3() {
	if (map.value === undefined) return;
	clearTracksV3();

	const tracks = mapStore.tracksV3;
	if (tracks.length === 0) return;

	const overlays: Record<string, L.Layer> = {};
	tracks.forEach((track, index) => {
		// @ts-expect-error L.GPX is not typed
		const layer = new L.GPX(track.url, {
			async: true,
			polyline_options: { color: colorForTrackIndex(index), weight: 4 },
			marker_options: {
				startIconUrl: null,
				endIconUrl: null,
				shadowUrl: null,
			},
		}).on("error", function (e: { err: string }) {
			toast.add({ severity: "error", summary: trans("gallery.map.error_gpx"), detail: e.err, life: 3000 });
		});
		layer.addTo(map.value as L.Map);
		trackLayersV3.value.set(track.id, layer as L.Layer);
		overlays[escapeHtml(track.name)] = layer as L.Layer;
	});
	if (Object.keys(overlays).length > 0) {
		L.control.layers(undefined, overlays).addTo(map.value);
	}
}

/**
 * `bucketsV3`/`photosV3` resolve together (`Promise.all` in
 * `MapState.ts.fetchViewportNow()`), and which one actually renders depends
 * on both (`renderAggregateMarkers()` only draws when `photosV3` is empty) -
 * so either arriving re-evaluates both render functions, each a no-op
 * unless it decides it owns the current viewport.
 */
function renderMarkersForCurrentViewport() {
	renderAggregateMarkers();
	renderIndividualPhotos();
}

watch(() => mapStore.bucketsV3, renderMarkersForCurrentViewport);
watch(() => mapStore.photosV3, renderMarkersForCurrentViewport);
watch(
	() => mapStore.tracksV3,
	() => renderTracksV3(),
);

onUnmounted(() => {
	if (map.value !== undefined) {
		map.value.off("moveend zoomend", onMapViewportChanged);
	}
	clearAggregateMarkers();
	clearIndividualPhotos();
	clearTracksV3();
	mapStore.reset();
});

/**
 * Cluster badges reuse the exact `.leaflet-marker-photo` HTML/CSS
 * `composables/photo.ts`'s own default `iconCreateFunction` builds (byte for
 * byte, including the zero-width space separating the thumbnail `<div>` from
 * the count `<b>` - preserved for CSS/visual parity), but lazily loads the
 * representative child's thumbnail instead of assuming it's already
 * resolved - see `ensureThumbnailLoaded()`'s own doc comment for why.
 * `cluster.setIcon()` (inherited from `L.Marker`) redraws the currently
 * displayed badge once the fetch resolves - a no-op if the user has since
 * zoomed away and this exact cluster object is no longer on screen.
 */
function photoClusterIconCreateFunction(cluster: L.MarkerCluster): L.DivIcon {
	const representative = cluster.getAllChildMarkers()[0] as L.Marker & { photo?: MapPhotoEntry };
	const thumbnail = representative.photo?.thumbnail ?? UNRESOLVED_THUMBNAIL;

	// `photoLayer` is shared with the v2 (non-SoA) path, whose photos already
	// carry a real, synchronously-available thumbnail URL - only a v3 entry
	// still sitting on the shared placeholder sentinel needs lazy-loading at
	// all (v2 clusters must not pay for a redundant `ThumbAssetService` fetch
	// of an image it already has a perfectly good URL for).
	if (representative.photo !== undefined && thumbnail === UNRESOLVED_THUMBNAIL) {
		ensureThumbnailLoaded(representative.photo, () => {
			cluster.setIcon(photoClusterIconCreateFunction(cluster));
		});
	}

	return L.divIcon({
		className: "leaflet-marker-photo",
		html: `${buildPhotoIconHtml(thumbnail)}​<b>${cluster.getChildCount()}</b>`,
		iconSize: [40, 40],
	});
}

function open() {
	// Define how the photos on the map should look
	photoLayer.value = clusterFunc({ iconCreateFunction: photoClusterIconCreateFunction }).on("click", function (e: MapClickEvent) {
		const photo: MapPhotoEntry = {
			photoID: e.layer.photo.photoID,
			albumID: e.layer.photo.albumID,
			// Photo titles are free-text and not HTML-escaped by the API (the
			// JSON response is a raw-data contract, consumed by several
			// different renderers - Vue template interpolation already
			// auto-escapes on its own). `L.Util.template()`/`bindPopup()`
			// below is not HTML-aware, so escaping must happen here, at the
			// point of insertion - same reasoning as `escapeHtml(track.name)`
			// elsewhere in this file.
			name: escapeHtml(e.layer.photo.name),
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

	// Both `addContentsToMap()` (v2) and `renderIndividualPhotos()` (v3 SoA)
	// call `.add(photos)` on this layer, which only adds member markers - it
	// never attaches the layer GROUP itself to the map. Both used to rely on
	// their own `.addTo(map.value)` for that (or, in an earlier v3 SoA
	// iteration, skip it entirely via a bare `addLayer()`, silently making
	// every individual photo invisible). Attaching it here once,
	// unconditionally, covers every path.
	// @ts-expect-error photoLayer is created by leaflet.photo and is not typed
	photoLayer.value.addTo(map.value);
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
	// Leaflet's native layers control for the legend/visibility checkboxes.
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

definePanelShortcuts({
	escape: {
		usingInput: true,
		handler: () => goBack(),
	},
});

onMounted(() => {
	leftMenuStore.left_menu_open = false;
	// `isMapSoaActive` (mapInit()'s v2/v3 dispatch) reads `lycheeStore`'s
	// `is_struct_of_array_enabled`, which defaults to false until this load
	// resolves - chained so mapInit() never runs against that default.
	lycheeStore.load().then(() => {
		loadMapProvider();
	});
});
</script>
<style lang="css">
/*
 * Named custom properties for this file's Leaflet marker chrome - previously
 * repeated/scattered literal colors (pre-dates Feature 067; the two
 * `.leaflet-marker-photo*` rules are reused byte-for-byte from the v2 path,
 * FR-067-21 - only centralized here, values unchanged). `--lychee-map-*`
 * mirrors this codebase's existing scoped-custom-property convention (e.g.
 * `LycheeLoadingIcon.vue`'s `--lychee-loading-cycle`).
 */
:root {
	--lychee-map-marker-shadow: #888;
	--lychee-map-marker-badge-text: #555;
	--lychee-map-marker-badge-shadow: rgba(0, 0, 0, 0.4);
}

.leaflet-marker-photo {
	border: 2px solid #fff;
	box-shadow: 3px 3px 10px var(--lychee-map-marker-shadow);
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
	color: var(--lychee-map-marker-badge-text);
	background-color: #fff;
	border-radius: 8px;
	height: 12px;
	min-width: 12px;
	line-height: 12px;
	text-align: center;
	padding: 3px;
	box-shadow: 0 3px 14px var(--lychee-map-marker-badge-shadow);
}

/*
 * Feature 067 (I8): aggregate (cluster) count-badge marker, UI-067-01.
 * Background is the live theme primary token (`--ui-primary`, the same one
 * `TimelineDatesV3.vue`'s own playhead uses) - not a hardcoded hex copy of
 * it, so this marker tracks an admin's configured primary color instead of
 * silently drifting from it.
 */
.leaflet-marker-aggregate-badge {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 100%;
	border-radius: 9999px;
	background-color: var(--ui-primary);
	color: #ffffff;
	font-weight: 700;
	font-size: 0.65rem;
	box-shadow: 3px 3px 10px var(--lychee-map-marker-shadow);
}
</style>
