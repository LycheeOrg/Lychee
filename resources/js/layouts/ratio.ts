import { ChildNodeWithDataStyle } from "./types";

export function getRatio(items: ChildNodeWithDataStyle[]): number[] {
	// Compute ratio of each item, if not found fall back to square.
	const ratio = items.map(function (photo) {
		const height = photo.dataset?.height ?? 1;
		const width = photo.dataset?.width ?? 1;
		return height > 0 ? width / height : 1;
	});

	return ratio;
}
