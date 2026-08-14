import { computed, Ref } from "vue";
import { usePhotosStore } from "@/stores/PhotosState";

/**
 * Collects the tags which are already set on the photos targeted by a tagging
 * dialog.
 *
 * `commonTags` are the tags carried by *every* targeted photo, `partialTags`
 * the ones carried by only some of them. Both lists are deduplicated and
 * sorted by name.
 */
export function useExistingTags(photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>, photoIds: Ref<string[] | undefined>) {
	const photosStore = usePhotosStore();

	const targetedPhotos = computed<App.Http.Resources.Models.PhotoResource[]>(() => {
		if (photo.value !== undefined) {
			return [photo.value];
		}

		const ids = photoIds.value ?? [];
		return photosStore.photos.filter((p) => ids.includes(p.id));
	});

	// Number of targeted photos carrying each tag name.
	const tagCounts = computed<Map<string, number>>(() => {
		const counts = new Map<string, number>();
		targetedPhotos.value.forEach((p) => {
			// A tag cannot be set twice on the same photo, but we stay defensive
			// so that a duplicate never inflates the count above the photo count.
			new Set(p.tags.map((tag) => tag.name)).forEach((name) => counts.set(name, (counts.get(name) ?? 0) + 1));
		});
		return counts;
	});

	function sortedTagNames(isOnAllPhotos: boolean): string[] {
		return Array.from(tagCounts.value.entries())
			.filter((entry) => (entry[1] === targetedPhotos.value.length) === isOnAllPhotos)
			.map((entry) => entry[0])
			.sort((a, b) => a.localeCompare(b));
	}

	const commonTags = computed<string[]>(() => sortedTagNames(true));
	const partialTags = computed<string[]>(() => sortedTagNames(false));

	const hasExistingTags = computed<boolean>(() => tagCounts.value.size > 0);

	return {
		commonTags,
		partialTags,
		hasExistingTags,
	};
}
