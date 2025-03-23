import SearchService from "@/services/search-service";
import { TogglablesStateStore } from "@/stores/ModalsState";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { computed, ref, Ref } from "vue";

export function useSearch(albumId: Ref<string>, togglableStore: TogglablesStateStore, search_term: Ref<string>, search_page: Ref<number>) {
	const isSearching = ref(false);

	// Search results
	const albums = ref<App.Http.Resources.Models.ThumbAlbumResource[] | undefined>(undefined);
	const photos = ref<App.Http.Resources.Models.PhotoResource[] | undefined>(undefined);

	// Search configuration
	const searchMinimumLengh = ref<number | undefined>(undefined);
	const layout = ref<App.Enum.PhotoLayoutType>("square");

	const from = ref(0);
	const per_page = ref(0);
	const total = ref(0);

	const photoHeader = computed(() => {
		return sprintf(trans("gallery.search.photos"), total.value);
	});

	const albumHeader = computed(() => {
		if (albums.value === undefined) {
			return "";
		}
		return sprintf(trans("gallery.search.albums"), albums.value.length);
	});

	function searchInit() {
		SearchService.init(albumId.value).then((response) => {
			searchMinimumLengh.value = response.data.search_minimum_length;
			layout.value = response.data.photo_layout;
		});
	}

	function search(terms: string): Promise<void> {
		if (terms.length < (searchMinimumLengh.value ?? 3)) {
			albums.value = undefined;
			photos.value = undefined;
			return Promise.resolve();
		}

		togglableStore.search_album_id = albumId.value;
		search_term.value = terms;
		isSearching.value = true;
		return SearchService.search(albumId.value, search_term.value, search_page.value).then((response) => {
			albums.value = response.data.albums;
			photos.value = response.data.photos;
			from.value = response.data.from;
			per_page.value = response.data.per_page;
			total.value = response.data.total;
			isSearching.value = false;
		});
	}

	function refresh(): Promise<void> {
		isSearching.value = true;
		search_page.value = Math.ceil(from.value / per_page.value) + 1;
		return SearchService.search(albumId.value, search_term.value, search_page.value).then((response) => {
			albums.value = response.data.albums;
			photos.value = response.data.photos;
			from.value = response.data.from;
			per_page.value = response.data.per_page;
			total.value = response.data.total;
			isSearching.value = false;
		});
	}

	function clear() {
		albums.value = undefined;
		photos.value = undefined;
		from.value = 0;
		per_page.value = 0;
		total.value = 0;
	}

	return {
		layout,
		albums,
		photos,
		searchMinimumLengh,
		isSearching,
		from,
		per_page,
		total,
		photoHeader,
		albumHeader,
		searchInit,
		search,
		clear,
		refresh,
	};
}
