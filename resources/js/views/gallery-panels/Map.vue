<template>
	<Collapse :when="!is_full_screen">
		<Toolbar class="w-full border-0 h-14">
			<template #start>
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
			</template>
			<template #center>
				{{ $t(lycheeStore.title) }}
			</template>
			<template #end> </template>
		</Toolbar>
	</Collapse>
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
import Button from "primevue/button";
import { ref, Ref } from "vue";
import { Collapse } from "vue-collapsed";
import { useRouter } from "vue-router";
import L, { LatLngBoundsLiteral } from "leaflet";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet.markercluster/dist/leaflet.markercluster.js";
import "@lychee-org/leaflet.photo/Leaflet.Photo.js";
import "leaflet/dist/leaflet.css";
import "@lychee-org/leaflet.photo/Leaflet.Photo.css";
import "leaflet-gpx/gpx.js";
import { useToast } from "primevue/usetoast";
import Toolbar from "primevue/toolbar";
import { onKeyStroke } from "@vueuse/core";
import Constants from "@/services/constants";
import { useTogglablesStateStore } from "@/stores/ModalsState";

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

const props = defineProps<{
	albumid?: string;
}>();

const toast = useToast();
const router = useRouter();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();

function goBack() {
	if (props.albumid !== undefined) {
		router.push({ name: "album", params: { albumid: props.albumid } });
	} else {
		router.push({ name: "gallery" });
	}
}
const { is_full_screen } = storeToRefs(togglableStore);

// Map stuff.
const camera_date = trans("gallery.camera_date");
const map_provider = ref<App.Http.Resources.GalleryConfigs.MapProviderData | undefined>(undefined);
const map = ref(undefined) as Ref<L.Map | undefined>;
const bounds = ref<LatLngBoundsLiteral | undefined>(undefined);
const photoLayer = ref<any>(undefined);
const trackLayer = ref<any>(undefined);
const data = ref<App.Http.Resources.Collections.PositionDataResource | undefined>(undefined);

function loadMapProvider() {
	AlbumService.getMapProvider().then((data) => {
		map_provider.value = data.data;
		mapInit();
	});
}

function mapInit() {
	// @ts-ignore
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
	AlbumService.getMapData(props.albumid).then((mapData) => {
		data.value = mapData.data;
		addContentsToMap();
	});
}

function open() {
	// Define how the photos on the map should look
	// @ts-expect-error
	photoLayer.value = L.photo.cluster().on("click", function (e: any) {
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
				'<img class=" w-full h-auto" src="{url}" srcset="{url} 1x, {url2x} 2x" data-album-id="{albumID}" data-id="{photoID}"/>',
				'<div class=" pointer-events-none absolute w-full bottom-0 m-0 bg-gradient-to-t from-[#00000066] text-shadow" style="width:401px; bottom: 13px;">',
				'<h1 class=" min-h-[19px] mt-3 mb-1 ml-3 text-color text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">{name}</h1>',
				'<p class="block mt-0 mr-0 mb-2 ml-3 text-xs text-muted-color-emphasis">',
				'<span class="inline-block mx-2" title="' + camera_date + '">',
				'<svg class="inline-block h-3 w-3 fill-neutral-400"><use xlink:href="#camera-slr" /></svg>',
				"</span>",
				"{taken_at}</p>",
				"</div>",
			);
		} else {
			template = template.concat(
				'<img class=" w-full h-auto" src="{url}" data-album-id="{albumID}" data-id="{photoID}"/>',
				'<div class=" pointer-events-none absolute w-full bottom-0 m-0 bg-gradient-to-t from-[#00000066] text-shadow" style="width:401px; bottom: 13px;">',
				'<h1 class=" min-h-[19px] mt-3 mb-1 ml-3 text-color text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">{name}</h1>',
				'<p class="block mt-0 mr-0 mb-2 ml-3 text-xs text-muted-color-emphasis">',
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
	if (data.value.photos.length === 0) return;

	// Check initializations
	if (map.value === undefined) return;
	if (photoLayer.value === null) return;

	let photos: MapPhotoEntry[] = [];
	let min_lat: number | null = null;
	let min_lng: number | null = null;
	let max_lat: number | null = null;
	let max_lng: number | null = null;

	data.value.photos.forEach(function (element: App.Http.Resources.Models.PhotoResource) {
		if (element.latitude || element.longitude) {
			photos.push({
				lat: element.latitude,
				lng: element.longitude,
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
			if (element.latitude !== null && (min_lat === null || min_lat > element.latitude)) {
				min_lat = element.latitude;
			}
			if (element.longitude !== null && (min_lng === null || min_lng > element.longitude)) {
				min_lng = element.longitude;
			}
			if (element.latitude !== null && (max_lat === null || max_lat < element.latitude)) {
				max_lat = element.latitude;
			}
			if (element.longitude !== null && (max_lng === null || max_lng < element.longitude)) {
				max_lng = element.longitude;
			}
		}
	});

	// Add Photos to map
	photoLayer.value.add(photos).addTo(map.value);

	if (max_lat === null || min_lat === null || max_lng === null || min_lng === null) {
		return;
	}

	if (photos.length > 0) {
		// update map bounds
		const dist_lat = max_lat - min_lat;
		const dist_lng = max_lng - min_lng;
		bounds.value = [
			[min_lat - 0.1 * dist_lat, min_lng - 0.1 * dist_lng],
			[max_lat + 0.1 * dist_lat, max_lng + 0.1 * dist_lng],
		];
	}

	// add track
	if (data.value.track_url) {
		// @ts-expect-error
		trackLayer.value = new L.GPX(data.value.track_url, {
			async: true,
			marker_options: {
				startIconUrl: null,
				endIconUrl: null,
				shadowUrl: null,
			},
		})
			.on("error", function (e: any) {
				toast.add({ severity: "error", summary: trans("gallery.map.error_gpx"), detail: e.err, life: 3000 });
			})
			.on("loaded", function (e: any) {
				if (photos.length === 0) {
					// no photos, update map bound to center track
					bounds.value = e.target.getBounds();
					updateZoom();
				}
			});
		if (trackLayer.value !== undefined) {
			trackLayer.value.addTo(map.value);
		}
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

loadMapProvider();

onKeyStroke("Escape", () => {
	goBack();
});
</script>
