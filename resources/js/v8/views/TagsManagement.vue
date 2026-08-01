<template>
	<TagRenameDialog
		v-if="renameTarget !== undefined"
		v-model:visible="isRenameDialogVisible"
		:tag="renameTarget"
		@updated="
			closeRename();
			load();
		"
	/>
	<TagMergeDialog
		v-if="mergeFrom !== undefined && mergeInto !== undefined"
		v-model:visible="isMergeDialogVisible"
		:selected="mergeFrom"
		:into="mergeInto"
		@merged="
			clearSelection();
			load();
		"
	/>
	<TagDeleteDialog
		v-if="selectedTags.length > 0"
		v-model:visible="isDeleteDialogVisible"
		:tags="selectedTags"
		@deleted="
			clearSelection();
			load();
		"
	/>

	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("tags.title") }}
	</UHeader>
	<UCard v-if="tags !== undefined" class="p-9 mx-auto max-w-3xl" :ui="{ header: 'hidden' }">
		<div class="text-muted text-center mb-8" v-html="$t('tags.description')"></div>
		<div v-if="tags.length === 0" class="p-4 text-center">{{ $t("tags.no_tags") }}</div>
		<template v-else>
			<div v-if="canEdit" class="flex justify-end gap-4 mb-8">
				<UButton :disabled="!canMerge" variant="ghost" color="primary" @click="openMerge">
					<UIcon name="lucide:shrink" />
					{{ $t("tags.merge") }}
				</UButton>
				<UButton :disabled="!canDelete" variant="ghost" color="error" @click="openDelete">
					<UIcon name="lucide:trash" />
					{{ $t("tags.delete") }}
				</UButton>
			</div>
			<UTable
				:data="tags"
				:columns="columns"
				:meta="{ class: { tr: rowClass } }"
				:on-select="(_e: Event, row: TableRow<Tag>) => navigate(row.original.id)"
				class="w-full text-sm"
				:ui="{ td: 'px-2 py-1', th: 'px-2' }"
			>
				<template #select-cell="{ row }">
					<div class="text-center" @click.stop>
						<UCheckbox :model-value="isSelected(row.original.id)" @update:model-value="() => toggleSelect(row.original.id)" />
					</div>
				</template>
				<template #num_photos-cell="{ row }">
					<span class="text-muted">{{ row.original.num_photos > 0 ? row.original.num_photos : "" }}</span>
				</template>
				<template #num_albums-cell="{ row }">
					<span class="text-muted">{{ row.original.num_albums > 0 ? row.original.num_albums : "" }}</span>
				</template>
				<template #actions-cell="{ row }">
					<div @click.stop>
						<UButton
							variant="ghost"
							color="neutral"
							size="sm"
							icon="lucide:pencil"
							class="cursor-pointer"
							@click="openRenameFor(row.original)"
						/>
					</div>
				</template>
			</UTable>
		</template>
	</UCard>
	<div v-else class="flex justify-center items-center p-4">
		<LycheeLoadingIcon fast />
		<span class="ml-2">{{ $t("tags.loading") }}</span>
	</div>
</template>

<script setup lang="ts">
import { computed, onMounted } from "vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import { useTagsRefresher } from "@/composables/tags/tagsRefresher";
import { useTagsActions } from "@/v8/composables/tags/tagsActions";
import TagRenameDialog from "@/v8/components/forms/tags/TagRenameDialog.vue";
import TagMergeDialog from "@/v8/components/forms/tags/TagMergeDialog.vue";
import TagDeleteDialog from "@/v8/components/forms/tags/TagDeleteDialog.vue";
import { onKeyDown } from "@vueuse/core";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import type { TableColumn, TableRow } from "@nuxt/ui";

type Tag = App.Http.Resources.Tags.TagResource;

const router = useRouter();
const { tags, canEdit, load } = useTagsRefresher();
const {
	selectedTags,
	canMerge,
	canDelete,
	renameTarget,
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
} = useTagsActions(tags, router);

function rowClass(row: TableRow<Tag>): string {
	return isSelected(row.original.id) ? "cursor-pointer bg-primary/10" : "cursor-pointer hover:bg-elevated/50";
}

const columns = computed<TableColumn<Tag>[]>(() => {
	const cols: TableColumn<Tag>[] = [];

	if (canEdit.value) {
		cols.push({ id: "select", meta: { class: { td: "w-10" } } });
	}

	cols.push(
		{ accessorKey: "name", header: trans("tags.column_name") },
		{ id: "num_photos", header: trans("tags.column_photos"), meta: { class: { th: "text-center w-24", td: "text-center w-24" } } },
		{ id: "num_albums", header: trans("tags.column_albums"), meta: { class: { th: "text-center w-24", td: "text-center w-24" } } },
	);

	if (canEdit.value) {
		cols.push({ id: "actions", meta: { class: { td: "w-10" } } });
	}

	return cols;
});

onMounted(load);

onKeyDown("Escape", clearSelection);
</script>
