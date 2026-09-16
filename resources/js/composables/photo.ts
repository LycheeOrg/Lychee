/* eslint-disable @typescript-eslint/no-explicit-any */
import L from "leaflet";

// `L.FeatureGroup.extend`/`L.MarkerClusterGroup.extend` must not run at module
// load: this module is real ESM and gets evaluated (bundler-order) before the
// `leaflet.markercluster` UMD side-effect import - which the bundler wraps as
// a lazily-invoked CJS factory - has actually run and patched `L`. Building
// these classes lazily, on first `photosLayerFunc()`/`clusterFunc()` call,
// guarantees that patch has already happened by then.
let PhotosLayer: any;

function getPhotosLayer() {
	if (PhotosLayer === undefined) {
		PhotosLayer = L.FeatureGroup.extend({
			options: {
				icon: {
					iconSize: [40, 40],
				},
			},

			initialize: function (photos: any, options: any) {
				L.setOptions(this, options);
				// @ts-expect-error initialize does exists
				L.FeatureGroup.prototype.initialize.call(this, photos);
			},

			addLayers: function (photos: any) {
				if (photos) {
					for (let i = 0, len = photos.length; i < len; i++) {
						this.addLayer(photos[i]);
					}
				}
				return this;
			},

			addLayer: function (photo: any) {
				L.FeatureGroup.prototype.addLayer.call(this, this.createMarker(photo));
			},

			createMarker: function (photo: any) {
				const marker: L.Marker & { photo?: any } = L.marker(photo, {
					icon: L.divIcon(
						L.extend(
							{
								html: '<div style="background-image: url(' + photo.thumbnail + ');"></div>​',
								className: "leaflet-marker-photo",
							},
							photo,
							this.options.icon,
						),
					),
					title: photo.caption || "",
				});
				marker.photo = photo;
				return marker;
			},
		});
	}
	return PhotosLayer;
}

const photosLayerFunc = function (photos: any, options: any) {
	return new (getPhotosLayer())(photos, options);
};

let Cluster: any;

function getCluster() {
	if (Cluster === undefined) {
		Cluster = L.MarkerClusterGroup.extend({
			options: {
				featureGroup: photosLayerFunc,
				maxClusterRadius: 100,
				showCoverageOnHover: false,
				// leaflet.markercluster@1.5.3 (last released 2021) predates
				// leaflet@1.9.4's marker lifecycle changes: its animated
				// add/remove path (used when clusters merge/split on zoom)
				// can call a marker's `_animateZoom` after `_map` has already
				// been cleared mid-transition - "Cannot read properties of
				// null (reading '_latLngToNewLayerPoint')". Disabling
				// animation skips that whole code path; a known, low-risk
				// compatibility workaround, not a visual feature this app
				// relies on.
				animate: false,
				iconCreateFunction: function (cluster: any) {
					return new L.DivIcon(
						L.extend(
							{
								className: "leaflet-marker-photo",
								html:
									'<div style="background-image: url(' +
									cluster.getAllChildMarkers()[0].photo.thumbnail +
									');"></div>​<b>' +
									cluster.getChildCount() +
									"</b>",
							},
							this.icon,
						),
					);
				},
				icon: {
					iconSize: new L.Point(40, 40),
				},
			},

			initialize: function (options: any) {
				options = L.Util.setOptions(this, options);
				// @ts-expect-error initialize does exists
				L.MarkerClusterGroup.prototype.initialize.call(this);
				this._photos = options.featureGroup(null, options);
			},

			add: function (photos: any) {
				this.addLayer(this._photos.addLayers(photos));
				return this;
			},

			clear: function () {
				this._photos.clearLayers();
				this.clearLayers();
			},

			// Real `L.Marker` instances `createMarker()` built for the photos
			// passed to `add()` - `add()`/`addLayers()` only return `this`, so a
			// caller that needs to mutate a specific marker afterwards (e.g.
			// swapping in a lazily-resolved thumbnail once it loads) has no
			// other way to get a handle back to it.
			getPhotoMarkers: function () {
				return this._photos.getLayers();
			},
		});
	}
	return Cluster;
}

const clusterFunc = function (options: any) {
	return new (getCluster())(options);
};

export { photosLayerFunc, clusterFunc };
