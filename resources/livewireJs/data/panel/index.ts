import { searchPanel } from "./searchPanel";
import { photoListingPanel } from "./photoListingPanel";
import { photoFormPanel } from "./photoFormPanel";
import { photoSidebarPanel } from "./photoSidebarPanel";

export const panels = {
	photoFormPanel,
	searchPanel,
	photoListingPanel,
	photoSidebarPanel,
	[Symbol.iterator]: function* () {
		yield* Object.values(this);
	},
};
