<template>
	<!-- `isolate` keeps Leaflet's pane and control z-indexes below the sticky album toolbar.
	     `data-stop-drag-select`: pressing on the map pans it, it never starts the album's drag selection. -->
	<div class="relative isolate w-full h-[30svh] min-h-64" data-stop-drag-select="true">
		<div ref="container" class="h-full w-full" />
	</div>
</template>
<script setup lang="ts">
import { onBeforeUnmount, onMounted, useTemplateRef } from "vue";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "leaflet-gpx/gpx.js";
import AlbumService from "@/services/album-service";
import MapV3Service, { type MapPointResource } from "@/services/map-v3-service";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useAppToast } from "@/v8/composables/useAppToast";

/**
 * Feature 086 — the album header drawn as a map of the album's photos: one
 * dot per photo (no clustering), the album's GPX tracks, title in the hero
 * card below. Lazy-loaded by `AlbumHero.vue` so Leaflet stays out of the
 * album page chunk.
 */
const props = defineProps<{
	albumId: string;
	tracks: App.Http.Resources.Models.TrackResource[];
}>();

// Same palette as the Map page (`Map.vue`).
const TRACK_COLORS = ["#e6194b", "#3cb44b", "#4363d8", "#f58231", "#911eb4", "#42d4f4", "#f032e6", "#bfef45"];
// Keeps a single photo, or a tight group, from zooming to street level.
const MAX_FIT_ZOOM = 14;
const FIT_PADDING: L.PointExpression = [24, 24];
// Leaflet's own default path colour, used when the theme colour cannot be read.
const FALLBACK_COLOR = "#3388ff";

const router = useRouter();
const toast = useAppToast();
const container = useTemplateRef<HTMLDivElement>("container");

let map: L.Map | undefined = undefined;
let isUnmounted = false;

function primaryColor(): string {
	const value = getComputedStyle(document.documentElement).getPropertyValue("--ui-primary").trim();
	return value !== "" ? value : FALLBACK_COLOR;
}

/**
 * Wheel zoom stays off so the wheel scrolls the page. On touch devices the
 * map cannot be dragged and zooming keeps the centre, so a drag scrolls the
 * page too (FR-086-08).
 */
function createMap(element: HTMLElement, provider: App.Http.Resources.GalleryConfigs.MapProviderData): L.Map {
	const isTouch = isTouchDevice();
	const leafletMap = L.map(element, {
		scrollWheelZoom: false,
		dragging: !isTouch,
		touchZoom: isTouch ? "center" : true,
		doubleClickZoom: isTouch ? "center" : true,
	});
	L.tileLayer(provider.layer, {
		attribution: provider.attribution,
		referrerPolicy: "origin",
	}).addTo(leafletMap);
	leafletMap.fitWorld();

	return leafletMap;
}

function openPhoto(albumId: string, photoId: string): void {
	router.push({ name: "album", params: { albumId, photoId } });
}

/**
 * One canvas-drawn dot per photo (NFR-086-06), then fit the map to them.
 */
function drawPoints(leafletMap: L.Map, points: MapPointResource): void {
	const renderer = L.canvas();
	const fillColor = primaryColor();
	const bounds = L.latLngBounds([]);

	points.ids.forEach((photoId, i) => {
		const position = L.latLng(points.latitudes[i], points.longitudes[i]);
		bounds.extend(position);
		L.circleMarker(position, { renderer, radius: 5, weight: 1, color: "#ffffff", fillColor, fillOpacity: 0.9 })
			.on("click", () => openPhoto(points.album_ids[i] ?? props.albumId, photoId))
			.addTo(leafletMap);
	});

	if (bounds.isValid()) {
		leafletMap.fitBounds(bounds, { padding: FIT_PADDING, maxZoom: MAX_FIT_ZOOM });
	}
}

/**
 * Lines only: no start, end or waypoint pins.
 */
function drawTracks(leafletMap: L.Map): void {
	props.tracks.forEach((track, index) => {
		// @ts-expect-error L.GPX is not typed
		const layer = new L.GPX(track.url, {
			async: true,
			markers: { startIcon: null, endIcon: null },
			gpx_options: { parseElements: ["track", "route"] },
			polyline_options: { color: TRACK_COLORS[index % TRACK_COLORS.length], weight: 3 },
		}).on("error", (e: { err: string }) => {
			toast.add({ severity: "error", summary: trans("gallery.map.error_gpx"), detail: e.err, life: 3000 });
		});
		layer.addTo(leafletMap);
	});
}

onMounted(async () => {
	const [provider, points] = await Promise.all([AlbumService.getMapProvider(), MapV3Service.getAlbumPoints(props.albumId)]);
	if (isUnmounted || container.value === null) {
		return;
	}

	map = createMap(container.value, provider.data);
	drawTracks(map);
	drawPoints(map, points.data);
});

onBeforeUnmount(() => {
	isUnmounted = true;
	map?.remove();
	map = undefined;
});
</script>
