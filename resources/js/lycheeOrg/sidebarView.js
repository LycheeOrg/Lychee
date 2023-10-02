import L from "leaflet";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet.markercluster/dist/leaflet.markercluster.js";
import "@lychee-org/leaflet.photo/Leaflet.Photo.js";
import "leaflet/dist/leaflet.css";
import "@lychee-org/leaflet.photo/Leaflet.Photo.css";

export default { sidebarView };

export function sidebarView(layer_val, attribution_val, latitude_val, longitude_val, img_direction_val, url_asset = "") {
	return {
		layer: layer_val,
		attribution: attribution_val,
		latitude: latitude_val,
		longitude: longitude_val,
		img_direction: img_direction_val,
		url_asset: url_asset,

		displayOnMap() {
			// Leaflet searches for icon in same directory as js file -> paths needs
			// to be overwritten
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: this.url_asset + "img/marker-icon-2x.png",
				iconUrl: this.url_asset + "img/marker-icon.png",
				shadowUrl: this.url_asset + "img/marker-shadow.png",
			});

			const myMap = L.map("leaflet_map_single_photo").setView([this.latitude, this.longitude], 13);

			L.tileLayer(this.layer, { attribution: this.attribution }).addTo(myMap);

			if (this.img_direction === null) {
				// Add Marker to map, direction is not set
				L.marker([this.latitude, this.longitude]).addTo(myMap);
			} else {
				// Add Marker, direction has been set
				const viewDirectionIcon = L.icon({
					iconUrl: this.url_asset + "img/view-angle-icon.png",
					iconRetinaUrl: this.url_asset + "img/view-angle-icon-2x.png",
					iconSize: [100, 58], // size of the icon
					iconAnchor: [50, 49], // point of the icon which will correspond to marker's location
				});
				const marker = L.marker([this.latitude, this.longitude], { icon: viewDirectionIcon }).addTo(myMap);
				marker.setRotationAngle(this.img_direction);
			}
		},
	};
}
