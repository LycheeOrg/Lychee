<template>
	<UInputMenu
		id="tags"
		v-model="selected"
		v-model:search-term="searchTerm"
		:items="suggestions"
		multiple
		ignore-filter
		:create-item="props.add ? { position: 'top', when: 'always' } : false"
		class="w-full"
		:placeholder="(modelValue?.length ?? 0) === 0 ? (props.placeholder ?? '') : ''"
		@update:model-value="($event: string[]) => emits('updated', $event)"
		@create="addTag"
	>
		<template #item-label="{ item }">
			{{ item }}<span class="text-muted ltr:ml-2 rtl:mr-2">({{ getNum(item as string) }})</span>
		</template>
		<template #create-item-label="{ item }">
			<span class="flex items-center gap-1"><UIcon name="lucide:plus" />{{ item.trim() }}</span>
		</template>
		<template #empty>
			{{ $t("tags.no_tags") }}
		</template>
	</UInputMenu>
</template>
<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import TagsService from "@/services/tags-service";
import { useAppToast } from "@/v8/composables/useAppToast";

const toast = useAppToast();
const emits = defineEmits(["updated"]);
const props = defineProps<{
	placeholder?: string;
	add: boolean;
}>();

const modelValue = defineModel<string[] | null | undefined>();

// UInputMenu does not accept null.
const selected = computed<string[]>({
	get: () => modelValue.value ?? [],
	set: (value) => {
		modelValue.value = value;
	},
});

// List of all tags fetched from the server, with the number of photos under each.
const tags = ref<App.Http.Resources.Tags.TagResource[]>([]);

const searchTerm = ref("");

// Tags starting with the current input (case-insensitive), all of them when the input is empty.
const suggestions = computed<string[]>(() => {
	const query = searchTerm.value.trim().toLowerCase();
	return tags.value.filter((tag) => tag.name.toLowerCase().startsWith(query)).map((tag) => tag.name);
});

function getNum(tag: string): number {
	return tags.value.find((t) => t.name === tag)?.num_photos ?? 0;
}

function addTag(input: string): void {
	const name = input.trim();
	const current = selected.value;
	searchTerm.value = "";
	if (name === "" || current.includes(name)) {
		return;
	}

	selected.value = [...current, name];
	emits("updated", selected.value);
}

function fetchTags(): void {
	TagsService.list()
		.then((response) => {
			tags.value = response.data.tags;
		})
		.catch(() => {
			toast.add({
				severity: "error",
				summary: "Error",
				detail: "Failed to fetch tags.",
				life: 3000,
			});
		});
}

onMounted(() => {
	fetchTags();
});
</script>
