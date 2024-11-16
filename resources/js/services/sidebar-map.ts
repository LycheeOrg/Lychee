import L from "leaflet";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet.markercluster/dist/leaflet.markercluster.js";
import "@lychee-org/leaflet.photo/Leaflet.Photo.js";
import "leaflet/dist/leaflet.css";
import "@lychee-org/leaflet.photo/Leaflet.Photo.css";
import { Ref, ref } from "vue";
import AlbumService from "./album-service";
import Constants from "./constants";

export default class SidebarMap {
	layer: string;
	attribution: string;

	constructor() {
		this.layer = "";
		this.attribution = "";
	}

	displayOnMap(latitude: number, longitude: number) {
		const mapData = document.getElementById("leaflet_map_single_photo");
		this.layer = mapData?.dataset.layer ?? "";
		this.attribution = mapData?.dataset.provider ?? "";

		// Leaflet searches for icon in same directory as js file -> paths needs
		// to be overwritten
		// @ts-expect-error
		delete L.Icon.Default.prototype._getIconUrl;
		L.Icon.Default.mergeOptions({
			iconRetinaUrl: Constants.BASE_URL + "/img/marker-icon-2x.png",
			iconUrl: Constants.BASE_URL + "/img/marker-icon.png",
			shadowUrl: Constants.BASE_URL + "/img/marker-shadow.png",
		});

		// kill the map if it exists
		const container = L.DomUtil.get("leaflet_map_single_photo");
		if (container !== null) {
			// @ts-expect-error
			container._leaflet_id = null;
		}

		const myMap = L.map("leaflet_map_single_photo").setView([latitude, longitude], 13);

		L.tileLayer(this.layer, { attribution: this.attribution }).addTo(myMap);

		// Add Marker to map, direction is not set
		L.marker([latitude, longitude]).addTo(myMap);
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
		if (latitude.value && longitude.value && map.value) {
			map.value.displayOnMap(latitude.value, longitude.value);
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
