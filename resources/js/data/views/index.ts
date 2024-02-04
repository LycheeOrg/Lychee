import { albumView } from "./albumView";
import { mapView } from "./mapView";
import { uploadView } from "./uploadView";
import { photoView } from "./photoView";
import { dropboxView } from "./dropboxView";

export const views = {
	albumView,
	photoView,
	mapView,
	uploadView,
	dropboxView,
	[Symbol.iterator]: function* () {
		yield* Object.values(this);
	},
};
