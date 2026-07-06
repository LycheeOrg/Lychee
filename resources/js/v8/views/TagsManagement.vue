<template>
	<TagRenameDialog
		v-if="selected !== undefined"
		v-model:visible="isRenameDialogVisible"
		:tag="selected"
		@updated="
			selected = undefined;
			load();
		"
	/>
	<TagMergeDialog
		v-if="selected !== undefined && into !== undefined"
		v-model:visible="isMergeDialogVisible"
		:selected="selected"
		:into="into"
		@merged="
			selected = undefined;
			into = undefined;
			load();
		"
	/>
	<TagDeleteDialog
		v-if="selected !== undefined"
		v-model:visible="isDeleteDialogVisible"
		:tag="selected"
		@deleted="
			selected = undefined;
			load();
		"
	/>

	<div class="w-full border-0 h-14 flex items-center justify-between px-2">
		<OpenLeftMenu />
		<span class="absolute left-1/2 -translate-x-1/2">{{ $t("tags.title") }}</span>
	</div>
	<UCard v-if="tags !== undefined" class="p-9 mx-auto max-w-3xl" :ui="{ header: 'hidden' }">
		<div class="text-muted text-center mb-8" v-html="$t('tags.description')"></div>
		<div v-if="tags.length === 0" class="p-4 text-center">{{ $t("tags.no_tags") }}</div>
		<template v-else>
			<div v-if="canEdit" class="flex justify-end gap-4 mb-8">
				<UButton :variant="isEditing ? 'solid' : 'ghost'" color="primary" size="sm" @click="toggleEditing">
					<UIcon name="prime:pencil" />
					{{ $t("tags.rename") }}
				</UButton>
				<UButton :variant="isMerging ? 'solid' : 'ghost'" color="primary" size="sm" @click="toggleMerging">
					<UIcon name="prime:arrow-down-left-and-arrow-up-right-to-center" />
					{{ $t("tags.merge") }}
				</UButton>
				<UButton :variant="isDeleting ? 'solid' : 'ghost'" color="error" size="sm" @click="toggleDeleting">
					<UIcon name="prime:trash" />
					{{ $t("tags.delete") }}
				</UButton>
			</div>
			<div class="flex justify-center items-center flex-wrap gap-y-6 gap-x-6">
				<template v-for="tag in tags" :key="tag.id">
					<UChip v-if="tag.num > 0" :text="tag.num" size="lg">
						<UBadge
							color="neutral"
							variant="soft"
							size="lg"
							:class="{
								'pr-3 shadow text-highlighted cursor-pointer border rounded-full': true,
								'border-transparent': selected?.id !== tag.id,
								'hover:border-primary': isEditing || isMerging,
								'hover:bg-red-800': isDeleting,
								'border-primary': (selected?.id === tag.id || into?.id === tag.id) && (isEditing || isMerging),
								'border-red-800': selected?.id === tag.id && isDeleting,
							}"
							@click="handle(tag.id)"
							>{{ tag.name }}</UBadge
						>
					</UChip>
					<UBadge
						v-else
						color="neutral"
						variant="soft"
						size="lg"
						:class="{
							'shadow text-highlighted cursor-pointer rounded-full': true,
							'hover:bg-red-800': isDeleting,
						}"
						@click="handle(tag.id)"
						>{{ tag.name }}</UBadge
					>
				</template>
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
import { useTagsActions } from "@/composables/tags/tagsActions";
import TagRenameDialog from "@/v8/components/forms/tags/TagRenameDialog.vue";
import TagMergeDialog from "@/v8/components/forms/tags/TagMergeDialog.vue";
import TagDeleteDialog from "@/v8/components/forms/tags/TagDeleteDialog.vue";
import { onKeyDown } from "@vueuse/core";
import { useRouter } from "vue-router";

const router = useRouter();
const { tags, canEdit, load } = useTagsRefresher();
const {
	isEditing,
	isMerging,
	isDeleting,
	selected,
	into,
	isRenameDialogVisible,
	isMergeDialogVisible,
	isDeleteDialogVisible,
	handle,
	toggleEditing,
	toggleMerging,
	toggleDeleting,
} = useTagsActions(tags, router);

onMounted(load);

onKeyDown("Escape", () => {
	selected.value = undefined;
	into.value = undefined;
	isEditing.value = false;
	isMerging.value = false;
	isDeleting.value = false;
});
</script>
