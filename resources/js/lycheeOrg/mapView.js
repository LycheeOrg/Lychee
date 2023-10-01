import L from "leaflet";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet-rotatedmarker/leaflet.rotatedMarker.js";
import "leaflet.markercluster/dist/leaflet.markercluster.js";
import "@lychee-org/leaflet.photo/Leaflet.Photo.js";
import "leaflet/dist/leaflet.css";
import "@lychee-org/leaflet.photo/Leaflet.Photo.css";

export default { mapView };

/**
 * @typedef MapProvider
 * @property {string} layer - URL pattern for map tile
 * @property {string} attribution - HTML with attribution
 */

/**
 * @typedef MapPhotoEntry
 *
 * @property {number} [lat] - latitude
 * @property {number} [lng] - longitude
 * @property {string} [thumbnail] - URL to the thumbnail
 * @property {string} [thumbnail2x] - URL to the high-res thumbnail
 * @property {string} url - URL to the small size-variant; quite a misnomer
 * @property {string} url2x - URL to the small, high-res size-variant; quite a misnomer
 * @property {string} name - the title of the photo
 * @property {string} taken_at - the takedate of the photo, formatted as a locale string
 * @property {string} albumID - the album ID
 * @property {string} photoID - the photo ID
 */

export function mapView(layer_val, attribution_val, camera_date_val, data_val = []) {
	return {
		/** @type {?L.Map} */
		map: null,
		layer: layer_val,
		attribution: attribution_val,
		camera_date: camera_date_val,
		photoLayer: null,
		trackLayer: null,
		data: data_val,
		/** @type {(?LatLngBounds|?number[][])} */
		bounds: null,
		/** @type {?string} */
		albumID: null,
		/** @type {?string} */
		map_provider: null,

		mapInit(albumID = null) {
			// Leaflet searches for icon in same directory as js file -> paths need
			// to be overwritten
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: "img/marker-icon-2x.png",
				iconUrl: "img/marker-icon.png",
				shadowUrl: "img/marker-shadow.png",
			});

			// Set initial view to (0,0)
			this.map = L.map("lychee_map_container").setView([0.0, 0.0], 2);

			L.tileLayer(this.layer, { attribution: this.attribution }).addTo(this.map);

			this.open();

			this.addContentsToMap();
		},

		open(albumID = null) {
			// Define how the photos on the map should look like
			this.photoLayer = L.photo.cluster().on("click", function (e) {
				/** @type {MapPhotoEntry} */
				const photo = {
					photoID: e.layer.photo.photoID,
					albumID: e.layer.photo.albumID,
					name: e.layer.photo.name,
					url: e.layer.photo.url,
					url2x: e.layer.photo.url2x,
					taken_at: lychee.locale.printDateTime(e.layer.photo.taken_at),
				};
				let template = "";

				// Retina version if available
				if (photo.url2x !== "") {
					template = template.concat(
						'<img class="image-leaflet-popup" src="{url}" ',
						'srcset="{url} 1x, {url2x} 2x" ',
						'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="' + this.camera_date + '">',
						build.iconic("camera-slr"),
						"</span><p>{taken_at}</p></div>"
					);
				} else {
					template = template.concat(
						'<img class="image-leaflet-popup" src="{url}" ',
						'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="' + this.camera_date + '">',
						build.iconic("camera-slr"),
						"</span><p>{taken_at}</p></div>"
					);
				}

				e.layer
					.bindPopup(L.Util.template(template, photo), {
						minWidth: 400,
					})
					.openPopup();
			});
		},

		/**
		 * Adds photos to the map.
		 *
		 * @param {(Album|TagAlbum|PositionData)} album
		 *
		 * @returns {void}
		 */
		addContentsToMap() {
			// check if empty
			if (!this.data.photos) return;

			/** @type {MapPhotoEntry[]} */
			let photos = [];

			/** @type {?number} */
			let min_lat = null;
			/** @type {?number} */
			let min_lng = null;
			/** @type {?number} */
			let max_lat = null;
			/** @type {?number} */
			let max_lng = null;

			this.data.photos.forEach(
				/** @param {Photo} element */ function (element) {
					if (element.latitude || element.longitude) {
						photos.push({
							lat: element.latitude,
							lng: element.longitude,
							thumbnail: element.size_variants.thumb !== null ? element.size_variants.thumb.url : "img/placeholder.png",
							thumbnail2x: element.size_variants.thumb2x !== null ? element.size_variants.thumb2x.url : null,
							url: element.size_variants.small !== null ? element.size_variants.small.url : element.url,
							url2x: element.size_variants.small2x !== null ? element.size_variants.small2x.url : null,
							name: element.title,
							taken_at: element.taken_at,
							albumID: element.album_id,
							photoID: element.id,
						});

						// Update min/max lat/lng
						if (min_lat === null || min_lat > element.latitude) {
							min_lat = element.latitude;
						}
						if (min_lng === null || min_lng > element.longitude) {
							min_lng = element.longitude;
						}
						if (max_lat === null || max_lat < element.latitude) {
							max_lat = element.latitude;
						}
						if (max_lng === null || max_lng < element.longitude) {
							max_lng = element.longitude;
						}
					}
				}
			);

			// Add Photos to map
			this.photoLayer.add(photos).addTo(this.map);

			if (photos.length > 0) {
				// update map bounds
				const dist_lat = max_lat - min_lat;
				const dist_lng = max_lng - min_lng;
				this.bounds = [
					[min_lat - 0.1 * dist_lat, min_lng - 0.1 * dist_lng],
					[max_lat + 0.1 * dist_lat, max_lng + 0.1 * dist_lng],
				];
			}

			// add track
			if (this.data.track_url) {
				this.trackLayer = new L.GPX(this.data.track_url, {
					async: true,
					marker_options: {
						startIconUrl: null,
						endIconUrl: null,
						shadowUrl: null,
					},
				})
					.on("error", function (e) {
						this.$wire.$dispatch("notify", { msg: "Error loading GPX file:" + e.err, type: "error" });
					})
					.on("loaded", function (e) {
						if (photos.length === 0) {
							// no photos, update map bound to center track
							this.bounds = e.target.getBounds();
							this.updateZoom();
						}
					});
				this.trackLayer.addTo(this.map);
			}

			// Update Zoom and Position
			this.updateZoom();
		},

		// Adjusts zoom and position of map to show all images
		updateZoom() {
			if (this.bounds) {
				this.map.fitBounds(this.bounds);
			} else {
				this.map.fitWorld();
			}
		},
	};
}
