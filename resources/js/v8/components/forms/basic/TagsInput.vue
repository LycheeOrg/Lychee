<template>
	<UInputTags
		id="tags"
		v-model="modelValue"
		class="w-full"
		:placeholder="(modelValue?.length ?? 0) === 0 ? (props.placeholder ?? '') : ''"
		@update:model-value="($event: string[]) => emits('updated', $event)"
	/>
</template>
<script setup lang="ts">
import { onMounted, ref } from "vue";
import TagsService from "@/services/tags-service";
import { useAppToast } from "@/v8/composables/useAppToast";

const toast = useAppToast();
const emits = defineEmits(["updated"]);
const props = defineProps<{
	placeholder?: string;
	add: boolean;
}>();

const modelValue = defineModel<string[] | null | undefined>();

// List of all tags fetched from the server (kept for parity with v7, not
// currently surfaced as suggestions since UInputTags has no dropdown).
const tags = ref<App.Http.Resources.Tags.TagResource[]>([]);

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
