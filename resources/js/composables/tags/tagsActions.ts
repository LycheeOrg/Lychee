import { Ref, ref } from "vue";

export function useTagsActions(tags: Ref<App.Http.Resources.Tags.TagResource[] | undefined>) {
	// Mode toggles
	const isEditing = ref(false);
	const isMerging = ref(false);
	const isDeleting = ref(false);

	// Selected tag tracking
	const into = ref<App.Http.Resources.Tags.TagResource | undefined>(undefined);
	const selected = ref<App.Http.Resources.Tags.TagResource | undefined>(undefined);

	// Dialog controls
	const isRenameDialogVisible = ref(false);
	const isDeleteDialogVisible = ref(false);
	const isMergeDialogVisible = ref(false);

	function getTagById(id: number): App.Http.Resources.Tags.TagResource | undefined {
		return tags.value?.find((tag) => tag.id === id);
	}

	function handle(tagId: number) {
		// Handle renaming logic
		const tag = getTagById(tagId);

		if (tag === undefined) {
			console.warn(`Tag with ID ${tagId} not found.`);
			return;
		}

		if (isEditing.value) {
			if (tag) {
				selected.value = tag;
				isRenameDialogVisible.value = true;
			}
			return;
		}

		if (isMerging.value) {
			if (selected.value === undefined) {
				selected.value = tag;
				return;
			}
			if (selected.value.id === tag.id) {
				// Clicked the same tag, deselect it
				selected.value = undefined;
				return;
			}

			into.value = tag;
			isMergeDialogVisible.value = true;
			return;
		}

		if (isDeleting.value) {
			isDeleteDialogVisible.value = true;
			selected.value = tag;
			return;
		}

		// Default action could be to show photos with this tag
		console.log(`View photos with tag ID: ${tagId}`);
	}

	function toggleEditing() {
		isMerging.value = false;
		isDeleting.value = false;
		isEditing.value = !isEditing.value;

		// Reset selections when exiting this mode
		if (!isEditing.value) {
			selected.value = undefined;
		}
	}

	function toggleMerging() {
		isEditing.value = false;
		isDeleting.value = false;
		isMerging.value = !isMerging.value;

		// Reset selections when exiting this mode
		if (!isMerging.value) {
			selected.value = undefined;
			into.value = undefined;
		}
	}

	function toggleDeleting() {
		isEditing.value = false;
		isMerging.value = false;
		isDeleting.value = !isDeleting.value;

		// Reset selections when exiting this mode
		if (!isDeleting.value) {
			selected.value = undefined;
		}
	}

	return {
		isEditing,
		isMerging,
		isDeleting,
		selected,
		into,
		isRenameDialogVisible,
		isMergeDialogVisible,
		isDeleteDialogVisible,
		getTagById,
		handle,
		toggleEditing,
		toggleMerging,
		toggleDeleting,
	};
}
