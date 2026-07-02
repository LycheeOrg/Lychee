import { ref } from "vue";
import PeopleService from "@/services/people-service";

/**
 * Loads the full list of persons (all pages) for use in pickers/autocompletes
 * that need to filter or match against every known person client-side.
 */
export function usePeopleList() {
	const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
	const loading = ref(false);

	function fetchPage(page: number, accumulated: App.Http.Resources.Models.PersonResource[]): Promise<void> {
		return PeopleService.getPeople(page).then((response) => {
			accumulated.push(...response.data.data);
			if (page < response.data.last_page) {
				return fetchPage(page + 1, accumulated);
			}
			people.value = accumulated;
		});
	}

	function load(): Promise<void> {
		loading.value = true;
		return fetchPage(1, []).finally(() => {
			loading.value = false;
		});
	}

	return { people, loading, load };
}
