/* eslint-disable @typescript-eslint/no-explicit-any */
import L from "leaflet";

const PhotosLayer = L.FeatureGroup.extend({
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

const photosLayerFunc = function (photos: any, options: any) {
	// @ts-expect-error we are expecting 2 arguments
	return new PhotosLayer(photos, options);
};

const Cluster = L.MarkerClusterGroup.extend({
	options: {
		featureGroup: photosLayerFunc,
		maxClusterRadius: 100,
		showCoverageOnHover: false,
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
});

const clusterFunc = function (options: any) {
	// @ts-expect-error we do expect an argument
	return new Cluster(options);
};

export { photosLayerFunc, clusterFunc };
