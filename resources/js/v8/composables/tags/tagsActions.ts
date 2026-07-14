import { computed, Ref, ref } from "vue";
import { Router } from "vue-router";

export function useTagsActions(tags: Ref<App.Http.Resources.Tags.TagResource[] | undefined>, router: Router) {
	// Ordered so the first two checked tags map predictably to merge source/destination.
	const selectedIds = ref<number[]>([]);
	const renameTarget = ref<App.Http.Resources.Tags.TagResource | undefined>(undefined);

	const isRenameDialogVisible = ref(false);
	const isMergeDialogVisible = ref(false);
	const isDeleteDialogVisible = ref(false);

	function getTagById(id: number): App.Http.Resources.Tags.TagResource | undefined {
		return tags.value?.find((tag) => tag.id === id);
	}

	function isSelected(tagId: number): boolean {
		return selectedIds.value.includes(tagId);
	}

	function toggleSelect(tagId: number): void {
		selectedIds.value = isSelected(tagId) ? selectedIds.value.filter((id) => id !== tagId) : [...selectedIds.value, tagId];
	}

	function clearSelection(): void {
		selectedIds.value = [];
	}

	const selectedTags = computed<App.Http.Resources.Tags.TagResource[]>(() =>
		selectedIds.value.map((id) => getTagById(id)).filter((tag): tag is App.Http.Resources.Tags.TagResource => tag !== undefined),
	);

	const canMerge = computed(() => selectedTags.value.length === 2);
	const canDelete = computed(() => selectedTags.value.length >= 1);

	const mergeFrom = computed<App.Http.Resources.Tags.TagResource | undefined>(() => (canMerge.value ? selectedTags.value[0] : undefined));
	const mergeInto = computed<App.Http.Resources.Tags.TagResource | undefined>(() => (canMerge.value ? selectedTags.value[1] : undefined));

	function openRenameFor(tag: App.Http.Resources.Tags.TagResource): void {
		renameTarget.value = tag;
		isRenameDialogVisible.value = true;
	}

	function closeRename(): void {
		renameTarget.value = undefined;
	}

	function openMerge(): void {
		if (!canMerge.value) {
			return;
		}
		isMergeDialogVisible.value = true;
	}

	function openDelete(): void {
		if (!canDelete.value) {
			return;
		}
		isDeleteDialogVisible.value = true;
	}

	function navigate(tagId: number): void {
		router.push({ name: "tag", params: { tagId } });
	}

	return {
		selectedIds,
		selectedTags,
		renameTarget,
		canMerge,
		canDelete,
		mergeFrom,
		mergeInto,
		isRenameDialogVisible,
		isMergeDialogVisible,
		isDeleteDialogVisible,
		isSelected,
		toggleSelect,
		clearSelection,
		openRenameFor,
		closeRename,
		openMerge,
		openDelete,
		navigate,
	};
}
