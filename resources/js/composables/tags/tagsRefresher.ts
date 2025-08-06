import TagsService from "@/services/tags-service";
import { ref } from "vue";

export function useTagsRefresher() {
	const tags = ref<App.Http.Resources.Tags.TagResource[] | undefined>(undefined);
	const canEdit = ref(false);

	function load() {
		tags.value = undefined; // Reset tags before loading
		return TagsService.list()
			.then((response) => {
				tags.value = response.data.tags;
				canEdit.value = response.data.can_edit;
			})
			.catch((err) => {
				tags.value ??= [];
				console.error("Error loading tags:", err);
			});
	}

	return {
		tags,
		canEdit,
		load,
	};
}
