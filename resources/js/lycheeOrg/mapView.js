import L from 'leaflet'

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

export function mapView(
		layer,
		attribution,
		isFullscreen_val = false, parent_id_val = null, albumIDs_val = [], photoIDs_val = []) {
	return {
		/** @type {?L.Map} */
		map: null,
		photoLayer: null,
		trackLayer: null,
		/** @type {(?LatLngBounds|?number[][])} */
		bounds: null,
		/** @type {?string} */
		albumID: null,
		/** @type {?string} */
		map_provider: null,

		mapProviders: {
			/**
			 * @type {MapProvider}
			 */
			Wikimedia: {
				layer: "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png",
				attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>',
			},
			/**
			 * @type {MapProvider}
			 */
			"OpenStreetMap.org": {
				layer: "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
				attribution: `&copy; <a href="https://openstreetmap.org/copyright">${lychee.locale["OSM_CONTRIBUTORS"]}</a>`,
			},
			/**
			 * @type {MapProvider}
			 */
			"OpenStreetMap.de": {
				layer: "https://tile.openstreetmap.de/{z}/{x}/{y}.png ",
				attribution: `&copy; <a href="https://openstreetmap.org/copyright">${lychee.locale["OSM_CONTRIBUTORS"]}</a>`,
			},
			/**
			 * @type {MapProvider}
			 */
			"OpenStreetMap.fr": {
				layer: "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ",
				attribution: `&copy; <a href="https://openstreetmap.org/copyright">${lychee.locale["OSM_CONTRIBUTORS"]}</a>`,
			},
			/**
			 * @type {MapProvider}
			 */
			RRZE: {
				layer: "https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png",
				attribution: `&copy; <a href="https://openstreetmap.org/copyright">${lychee.locale["OSM_CONTRIBUTORS"]}</a>`,
			},
		},

		init (albumID = null) {
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: "img/marker-icon-2x.png",
				iconUrl: "img/marker-icon.png",
				shadowUrl: "img/marker-shadow.png",
			});
	
			// Set initial view to (0,0)
			this.map = L.map("lychee_map_container").setView([0.0, 0.0], 13);
	
			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution,
			}).addTo(this.map);
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
						'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="' + lychee.locale["CAMERA_DATE"] + '">',
						build.iconic("camera-slr"),
						"</span><p>{taken_at}</p></div>"
					);
				} else {
					template = template.concat(
						'<img class="image-leaflet-popup" src="{url}" ',
						'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="' + lychee.locale["CAMERA_DATE"] + '">',
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

		
		// Adjusts zoom and position of map to show all images
		updateZoom() {
			if (this.bounds) {
				this.map.fitBounds(this.bounds);
			} else {
				this.map.fitWorld();
			}
		}

	}
}