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
				<UButton :disabled="!canMerge" variant="ghost" color="primary" size="sm" @click="openMerge">
					<UIcon name="lucide:shrink" />
					{{ $t("tags.merge") }}
				</UButton>
				<UButton :disabled="!canDelete" variant="ghost" color="error" size="sm" @click="openDelete">
					<UIcon name="lucide:trash" />
					{{ $t("tags.delete") }}
				</UButton>
			</div>
			<div class="overflow-x-auto">
				<table class="w-full text-sm border-collapse">
					<thead>
						<tr class="border-b border-default text-left">
							<th v-if="canEdit" class="p-2 w-10"></th>
							<th class="p-2">{{ $t("tags.column_name") }}</th>
							<th class="p-2 w-24 text-center">{{ $t("tags.column_photos") }}</th>
							<th class="p-2 w-24 text-center">{{ $t("tags.column_albums") }}</th>
							<th v-if="canEdit" class="p-2 w-10"></th>
						</tr>
					</thead>
					<tbody class="divide-y divide-default">
						<tr v-for="tag in tags" :key="tag.id" :class="rowClass(tag.id)">
							<td v-if="canEdit" class="p-2 text-center" @click.stop>
								<UCheckbox :model-value="isSelected(tag.id)" @update:model-value="() => toggleSelect(tag.id)" />
							</td>
							<td class="p-2 cursor-pointer" @click="navigate(tag.id)">{{ tag.name }}</td>
							<td class="p-2 text-center text-muted cursor-pointer" @click="navigate(tag.id)">
								{{ tag.num_photos > 0 ? tag.num_photos : "" }}
							</td>
							<td class="p-2 text-center text-muted cursor-pointer" @click="navigate(tag.id)">
								{{ tag.num_albums > 0 ? tag.num_albums : "" }}
							</td>
							<td v-if="canEdit" class="p-2 text-center">
								<UButton
									variant="ghost"
									color="neutral"
									size="sm"
									icon="lucide:pencil"
									class="cursor-pointer"
									@click="openRenameFor(tag)"
								/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</template>
	</UCard>
	<div v-else class="flex justify-center items-center p-4">
		<Spinner />
		<span class="ml-2">{{ $t("tags.loading") }}</span>
	</div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useTagsRefresher } from "@/composables/tags/tagsRefresher";
import { useTagsActions } from "@/v8/composables/tags/tagsActions";
import TagRenameDialog from "@/v8/components/forms/tags/TagRenameDialog.vue";
import TagMergeDialog from "@/v8/components/forms/tags/TagMergeDialog.vue";
import TagDeleteDialog from "@/v8/components/forms/tags/TagDeleteDialog.vue";
import { onKeyDown } from "@vueuse/core";
import { useRouter } from "vue-router";

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

function rowClass(tagId: number) {
	return {
		"cursor-pointer": true,
		"hover:bg-elevated/50": true,
		"bg-primary/10": isSelected(tagId),
	};
}

onMounted(load);

onKeyDown("Escape", clearSelection);
</script>
