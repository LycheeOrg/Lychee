<template>
	<div v-if="availableTags.length > 0" class="album-tag-filter w-full flex flex-col">
		<div class="flex justify-end w-full items-center gap-3">
			<!-- Tag Selection (MultiSelect) -->
			<USelectMenu
				v-model="selectedTagIds"
				:items="availableTags"
				label-key="name"
				value-key="id"
				multiple
				:placeholder="$t('gallery.menus.tag_filter_label')"
				class="w-full max-w-md"
			/>

			<!-- Logic Toggle (OR/AND) -->
			<URadioGroup v-model="tagLogic" orientation="horizontal" :items="tagLogicItems" />

			<!-- Action Buttons -->
			<div class="flex">
				<UButton
					:label="$t('gallery.menus.tag_filter_apply')"
					icon="prime:filter"
					size="sm"
					class="rounded-r-none"
					color="neutral"
					:disabled="selectedTagIds.length === 0"
					@click="applyFilter"
				/>
				<UButton
					:label="$t('gallery.menus.tag_filter_clear')"
					icon="prime:times"
					size="sm"
					class="rounded-l-none"
					color="neutral"
					variant="outline"
					:disabled="!isFilterActive"
					@click="clearFilter"
				/>
			</div>
		</div>

		<!-- Active Filter Summary -->
		<div v-if="isFilterActive" class="text-xs text-neutral-600 dark:text-neutral-400 flex justify-end items-center gap-2">
			<UIcon name="prime:filter" />
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
import { trans } from "laravel-vue-i18n";
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

const tagLogicItems = computed(() => [
	{ label: trans("gallery.menus.tag_filter_logic_or"), value: "OR" },
	{ label: trans("gallery.menus.tag_filter_logic_and"), value: "AND" },
]);

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
			availableTags.value = response.data.tags;
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
