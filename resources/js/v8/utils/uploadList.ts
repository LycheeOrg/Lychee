/**
 * Helpers for the virtualized v8 upload list (Feature 087).
 *
 * Each `UploadingLine.vue` row runs its own upload and reports the result through an
 * event, which an unmounted row cannot emit. Rows whose upload is running are therefore
 * added to the virtualizer's range so they stay mounted wherever the list is scrolled.
 */
import type { Uploadable } from "@/composables/album/uploadEvents";

export type UploadStatus = Uploadable["status"];

export function hasEnded(status: UploadStatus): boolean {
	return status === "done" || status === "warning" || status === "error";
}

/** Ascending indices of the files being uploaded. */
export function uploadingIndexes(files: readonly Pick<Uploadable, "status">[]): number[] {
	const indexes: number[] = [];
	files.forEach((file, index) => {
		if (file.status === "uploading") {
			indexes.push(index);
		}
	});
	return indexes;
}

/** Ascending union of two ascending index lists, without duplicates. */
export function withPinnedIndexes(range: readonly number[], pinned: readonly number[]): number[] {
	const merged: number[] = [];
	let i = 0;
	let j = 0;
	while (i < range.length || j < pinned.length) {
		const next = j >= pinned.length || (i < range.length && range[i] <= pinned[j]) ? range[i++] : pinned[j++];
		if (merged[merged.length - 1] !== next) {
			merged.push(next);
		}
	}
	return merged;
}
