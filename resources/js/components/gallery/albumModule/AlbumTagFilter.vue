<template>
	<div v-if="availableTags.length > 0" class="album-tag-filter flex flex-col gap-3 p-4 bg-neutral-100 dark:bg-neutral-800 rounded">
		<!-- Filter Title -->
		<div class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
			{{ $t("gallery.menus.tag_filter_label") }}
		</div>

		<!-- Tag Selection (MultiSelect) -->
		<MultiSelect
			v-model="selectedTagIds"
			:options="availableTags"
			option-label="name"
			option-value="id"
			:placeholder="$t('gallery.menus.tag_filter_label')"
			:max-selected-labels="3"
			class="w-full"
			display="chip"
		/>

		<!-- Logic Toggle (OR/AND) -->
		<div class="flex items-center gap-4">
			<label class="flex items-center gap-2 cursor-pointer">
				<RadioButton v-model="tagLogic" input-id="logic-or" name="tag_logic" value="OR" />
				<span class="text-sm">{{ $t("gallery.menus.tag_filter_logic_or") }}</span>
			</label>
			<label class="flex items-center gap-2 cursor-pointer">
				<RadioButton v-model="tagLogic" input-id="logic-and" name="tag_logic" value="AND" />
				<span class="text-sm">{{ $t("gallery.menus.tag_filter_logic_and") }}</span>
			</label>
		</div>

		<!-- Action Buttons -->
		<div class="flex gap-2">
			<Button
				:label="$t('gallery.menus.tag_filter_apply')"
				icon="pi pi-filter"
				size="small"
				severity="secondary"
				:disabled="selectedTagIds.length === 0"
				@click="applyFilter"
			/>
			<Button
				:label="$t('gallery.menus.tag_filter_clear')"
				icon="pi pi-times"
				size="small"
				severity="secondary"
				outlined
				:disabled="!isFilterActive"
				@click="clearFilter"
			/>
		</div>

		<!-- Active Filter Summary -->
		<div v-if="isFilterActive" class="text-xs text-neutral-600 dark:text-neutral-400 flex items-center gap-2">
			<i class="pi pi-filter" />
			<span>{{
				$t("gallery.menus.tag_filter_active_summary", {
					count: selectedTagIds.length.toString(),
					logic: tagLogic,
				})
			}}</span>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import MultiSelect from "primevue/multiselect";
import RadioButton from "primevue/radiobutton";
import Button from "primevue/button";
import AlbumService from "@/services/album-service";

const props = defineProps<{
	albumId: string;
}>();

const emits = defineEmits<{
	apply: [payload: { tagIds: number[]; tagLogic: string }];
	clear: [];
}>();

// Reactive state
const availableTags = ref<App.Http.Resources.Tags.TagResource[]>([]);
const selectedTagIds = ref<number[]>([]);
const tagLogic = ref<string>("OR");
const isFilterActive = ref<boolean>(false);

// Computed
const isApplyDisabled = computed(() => selectedTagIds.value.length === 0);

// Methods
function applyFilter() {
	if (selectedTagIds.value.length === 0) {
		return;
	}

	isFilterActive.value = true;
	emits("apply", {
		tagIds: selectedTagIds.value,
		tagLogic: tagLogic.value,
	});
}

function clearFilter() {
	selectedTagIds.value = [];
	tagLogic.value = "OR";
	isFilterActive.value = false;
	emits("clear");
}

async function fetchTags() {
	AlbumService.getAlbumTags(props.albumId)
		.then((response) => {
			availableTags.value = response.data;
		})
		.catch((error) => {
			console.error("Failed to fetch album tags:", error);
			availableTags.value = [];
		});
}

// Lifecycle
onMounted(() => {
	fetchTags();
});
</script>

<style scoped>
.album-tag-filter {
	/* Component-specific styles if needed */
}
</style>
