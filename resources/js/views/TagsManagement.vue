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

	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("tags.title") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel v-if="tags !== undefined" class="border-none p-9 mx-auto max-w-3xl" pt:header:class="hidden">
		<div class="text-muted-color text-center mb-8" v-html="$t('tags.description')"></div>
		<div v-if="tags.length === 0" class="p-4 text-center">{{ $t("tags.no_tags") }}</div>
		<template v-else>
			<div v-if="canEdit" class="flex justify-end gap-4 mb-8">
				<Button :text="!isEditing" severity="primary" size="small" class="border-none" @click="toggleEditing">
					<i class="pi pi-pencil"></i>
					{{ $t("tags.rename") }}
				</Button>
				<Button :text="!isMerging" severity="primary" size="small" class="border-none" @click="toggleMerging">
					<i class="pi pi-arrow-down-left-and-arrow-up-right-to-center"></i>
					{{ $t("tags.merge") }}
				</Button>
				<Button :text="!isDeleting" severity="danger" size="small" class="border-none" @click="toggleDeleting">
					<i class="pi pi-trash"></i>
					{{ $t("tags.delete") }}
				</Button>
			</div>
			<div class="flex justify-center items-center flex-wrap gap-y-6 gap-x-6">
				<template v-for="tag in tags" :key="tag.id">
					<OverlayBadge v-if="tag.num > 0" :value="tag.num" size="small" class="outlined">
						<Tag
							severity="secondary"
							:value="tag.name"
							rounded
							:class="{
								'pr-3 shadow text-muted-color-emphasis cursor-pointer border': true,
								'border-transparent': selected?.id !== tag.id,
								'hover:border-primary-emphasis': isEditing || isMerging,
								'hover:bg-red-800': isDeleting,
								'border-primary-500': (selected?.id === tag.id || into?.id === tag.id) && (isEditing || isMerging),
								'border-red-800': selected?.id === tag.id && isDeleting,
							}"
							@click="handle(tag.id)"
						></Tag>
					</OverlayBadge>
					<Tag
						v-else
						severity="secondary"
						:value="tag.name"
						rounded
						:class="{
							'shadow text-muted-color-emphasis cursor-pointer': true,
							'hover:bg-red-800': isDeleting,
						}"
						@click="handle(tag.id)"
					></Tag>
				</template>
			</div>
		</template>
	</Panel>
	<div v-else class="flex justify-center items-center p-4">
		<ProgressSpinner />
		<span class="ml-2">{{ $t("tags.loading") }}</span>
	</div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import ProgressSpinner from "primevue/progressspinner";
import Tag from "primevue/tag";
import Panel from "primevue/panel";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Toolbar from "primevue/toolbar";
import OverlayBadge from "primevue/overlaybadge";
import Button from "primevue/button";
import { useTagsRefresher } from "@/composables/tags/tagsRefresher";
import { useTagsActions } from "@/composables/tags/tagsActions";
import TagRenameDialog from "@/components/forms/tags/TagRenameDialog.vue";
import TagMergeDialog from "@/components/forms/tags/TagMergeDialog.vue";
import TagDeleteDialog from "@/components/forms/tags/TagDeleteDialog.vue";
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
<style>
.dark .outlined .p-badge {
	--p-overlaybadge-outline-color: var(--p-surface-800);
}
</style>
