import L from "leaflet";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet.markercluster/dist/leaflet.markercluster.js";
import "leaflet/dist/leaflet.css";
import { ref } from "vue";
import AlbumService from "./album-service";
import Constants from "./constants";

export default class SidebarMap {
	resizeObserver: ResizeObserver | undefined;

	displayOnMap(latitude: number, longitude: number, layer: string, attribution: string) {
		// The map is torn down and rebuilt from scratch on every call (see
		// container._leaflet_id reset below), so any observer watching the
		// previous instance's container must be disconnected first.
		this.resizeObserver?.disconnect();

		// Leaflet searches for icon in same directory as js file -> paths needs
		// to be overwritten
		// @ts-expect-error Leaflet types are not aware of this method
		delete L.Icon.Default.prototype._getIconUrl;
		L.Icon.Default.mergeOptions({
			iconRetinaUrl: Constants.BASE_URL + "/img/marker-icon-2x.png",
			iconUrl: Constants.BASE_URL + "/img/marker-icon.png",
			shadowUrl: Constants.BASE_URL + "/img/marker-shadow.png",
		});

		// kill the map if it exists
		const container = L.DomUtil.get("leaflet_map_single_photo");
		if (container !== null) {
			// @ts-expect-error Leaflet types are not aware of this method
			container._leaflet_id = null;
		}

		const myMap = L.map("leaflet_map_single_photo").setView([latitude, longitude], 13);

		L.tileLayer(layer, {
			attribution: attribution,
			referrerPolicy: "origin",
		}).addTo(myMap);

		// Add Marker to map, direction is not set
		L.marker([latitude, longitude]).addTo(myMap);

		// The sidebar this map lives in is always mounted and shown/hidden via
		// CSS (an offcanvas transition), not conditional DOM mounting. When the
		// map is created before that transition/layout settles, Leaflet caches
		// a stale container size (e.g. zero) at construction time and never
		// re-measures on its own, leaving the tile pane blank with no errors.
		// Force a re-measure once layout settles, and keep watching for any
		// later size change (sidebar animation, window resize, etc).
		requestAnimationFrame(() => myMap.invalidateSize());
		this.resizeObserver = new ResizeObserver(() => myMap.invalidateSize());
		this.resizeObserver.observe(myMap.getContainer());
	}
}

export function useSidebarMap(latitudeValue: number | null, longitudeValue: number | null) {
	const latitude = ref(latitudeValue);
	const longitude = ref(longitudeValue);

	const map = ref<SidebarMap | undefined>(undefined);
	const map_provider = ref<App.Http.Resources.GalleryConfigs.MapProviderData | undefined>(undefined);

	function onMount() {
		if (!map.value) {
			map.value = new SidebarMap();
		}

		if (!map_provider.value) {
			AlbumService.getMapProvider().then((data) => {
				map_provider.value = data.data;
				load();
			});
		}
	}

	function load() {
		if (latitude.value && longitude.value && map.value && map_provider.value) {
			map.value.displayOnMap(latitude.value, longitude.value, map_provider.value.layer, map_provider.value.attribution);
		}
	}

	return {
		latitude,
		longitude,
		map,
		map_provider,
		load,
		onMount,
	};
}
