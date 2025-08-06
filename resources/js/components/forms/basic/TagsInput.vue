<template>
	<AutoComplete
		id="tags"
		v-model="modelValue"
		:suggestions="filteredTags"
		:forceSelection="true"
		multiple
		class="pt-3 border-b hover:border-b-0 w-full"
		pt:inputmultiple:class="w-full border-t-0 border-l-0 border-r-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
		:placeholder="modelValue?.length === 0 ? (props.placeholder ?? '') : ''"
		@update:modelValue="($event) => emits('updated', $event)"
		@complete="search"
	>
		<template #option="slotProps">
			<div class="flex items-center">
				{{ slotProps.option }}<span class="ltr:ml-2 rtl:mr-2">({{ getNum(slotProps.option) }})</span>
			</div>
		</template>
	</AutoComplete>
</template>
<script setup lang="ts">
import { onMounted, ref } from "vue";
import type { Nullable } from "@primevue/core";
import TagsService from "@/services/tags-service";
import { useToast } from "primevue/usetoast";
import AutoComplete, { AutoCompleteCompleteEvent } from "primevue/autocomplete";

const toast = useToast();
const emits = defineEmits(["updated"]);
const props = defineProps<{
	placeholder?: string;
	add: boolean;
}>();

// This is the model value for the tags input, which is an array of strings.
// We can't use App.Http.Resources.Tags.TagResource[] directly because it is not available from the image data.
const modelValue = defineModel<Nullable<string[]>>();

// List of all tags fetched from the server.
// This contains the full list of tags available in the system and the total number of photos under it.
const tags = ref<App.Http.Resources.Tags.TagResource[]>([]);

// This is the filtered list of tags, it needs to be string, to match the modelValue type.
const filteredTags = ref<string[]>([]);

// In order to display the number of photos, we need to search into the tags list.
// This is not optimal, maybe we can consider using a map later?
function getNum(tag: string) {
	const foundTag = tags.value.find((t) => t.name === tag);
	return foundTag ? foundTag.num : 0;
}

function fetchTags(): void {
	TagsService.get()
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

function search(event: AutoCompleteCompleteEvent) {
	setTimeout(() => {
		if (!event.query.trim().length) {
			filteredTags.value = tags.value.map((tag) => tag.name);
		} else {
			// We prefix the filteredTags with the current query to ensure that the user can add tags to the list.
			let filtered: string[] = [];
			if (props.add) {
				filtered = [event.query.trim()];
			}

			filteredTags.value = filtered.concat(
				...tags.value
					.filter((tag) => {
						return tag.name.toLowerCase().startsWith(event.query.toLowerCase());
					})
					.map((tag) => tag.name),
			);
		}
	}, 250);
}

onMounted(() => {
	fetchTags();
});
</script>
