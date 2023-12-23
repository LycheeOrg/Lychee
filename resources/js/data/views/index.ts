import { albumView } from "./albumView";
import { mapView } from "./mapView";
import { uploadView } from "./uploadView";
import { photoView } from "./photoView";

export const views = {
	albumView,
	photoView,
	mapView,
	uploadView,
	[Symbol.iterator]: function* () {
		yield* Object.values(this);
	},
};
