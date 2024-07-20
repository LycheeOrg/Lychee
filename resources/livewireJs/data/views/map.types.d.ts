import { AlpineComponent } from "alpinejs";
import { Album, PositionData, TagAlbum } from "../../lycheeOrg/backend";
import { LatLngBounds } from "leaflet";

export interface MapProvider {
	layer: string;
	attribution: string;
}

export interface MapPhotoEntry {
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
}

export type Map = AlpineComponent<{
	map: null | L.Map;
	layer: string;
	attribution: string;
	camera_date: string;
	photoLayer: null;
	trackLayer: null;
	data: Album | TagAlbum | PositionData;
	bounds: LatLngBounds | null | number[][];
	albumID: string | null;
	map_provider: string | null;
	mapInit: (albumID?: string | null) => void;
	open: (albumID?: string | null) => void;
	addContentsToMap: () => void;
	updateZoom: () => void;
}>;
