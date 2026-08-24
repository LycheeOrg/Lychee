import { computed, ref, watch, type ComputedRef, type Ref } from "vue";
import { useDebounceFn } from "@vueuse/core";
import { Router } from "vue-router";
import SearchService from "@/services/search-service";
import { ALL } from "@/config/constants";
import type { SpotlightItem } from "./types";

export type SpotlightRemoteSearch = {
	isRemoteSearching: Ref<boolean>;
	remoteGroupItems: ComputedRef<SpotlightItem[]>;
	ensureSearchMinLength: () => void;
	reset: () => void;
};

/**
 * Server-searched photos for the "remote" group, debounced and guarded against a stale
 * response landing after the user kept typing (or cleared the box) - see `generation`.
 */
export function useSpotlightRemoteSearch(searchTerm: Ref<string>, router: Router, close: () => void): SpotlightRemoteSearch {
	// Cached across opens: `SearchService.init` is itself response-cached, but this keeps us
	// from re-reading the response on every reopen once we already have the value.
	const searchMinLength = ref(2);
	let searchMinLengthRequested = false;

	function ensureSearchMinLength() {
		if (searchMinLengthRequested) {
			return;
		}
		searchMinLengthRequested = true;
		SearchService.init(ALL).then((response) => {
			searchMinLength.value = response.data.search_minimum_length;
		});
	}

	const isRemoteSearching = ref(false);
	const remotePhotos = ref<App.Http.Resources.Models.PhotoResource[]>([]);

	// Identifies a search request rather than its text: re-searching the same term (e.g. "cat",
	// then something else, then "cat" again) would make a plain term comparison mistake the
	// first (stale) request's response for the second's, since both share the same `searchTerm.value`.
	let remoteSearchGeneration = 0;

	const runRemoteSearch = useDebounceFn((term: string, generation: number) => {
		// Guards against a stale response landing after the user kept typing (or cleared the box).
		if (generation !== remoteSearchGeneration || term.length < searchMinLength.value) {
			return;
		}
		isRemoteSearching.value = true;
		SearchService.search(ALL, term)
			.then((response) => {
				if (generation !== remoteSearchGeneration) {
					return;
				}
				remotePhotos.value = response.data.photos;
			})
			.finally(() => {
				if (generation === remoteSearchGeneration) {
					isRemoteSearching.value = false;
				}
			});
	}, 300);

	watch(searchTerm, (term) => {
		remoteSearchGeneration++;
		if (term.trim().length < searchMinLength.value) {
			remotePhotos.value = [];
			isRemoteSearching.value = false;
			return;
		}
		// Cleared here (not just once the new response lands) so the "remote" group - which sets
		// `ignoreFilter: true` in `groups` - doesn't keep showing the previous term's results
		// while this one is still debouncing/in flight.
		remotePhotos.value = [];
		runRemoteSearch(term, remoteSearchGeneration);
	});

	function reset() {
		remotePhotos.value = [];
		isRemoteSearching.value = false;
	}

	const remoteGroupItems = computed<SpotlightItem[]>(() => [
		...remotePhotos.value.map((photo): SpotlightItem => ({
			label: photo.title,
			kind: "remote-photo",
			thumbUrl: photo.size_variants.thumb?.url ?? null,
			onSelect: () => {
				close();
				router.push({ name: "album", params: { albumId: photo.album_id ?? ALL, photoId: photo.id } });
			},
		})),
	]);

	return { isRemoteSearching, remoteGroupItems, ensureSearchMinLength, reset };
}
