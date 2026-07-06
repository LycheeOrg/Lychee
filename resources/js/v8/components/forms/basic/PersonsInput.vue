<template>
	<USelectMenu
		id="persons"
		v-model="uiValue"
		:items="people"
		multiple
		label-key="name"
		class="pt-3 border-b hover:border-b-0 w-full"
		:placeholder="(modelValue?.length ?? 0) === 0 ? (props.placeholder ?? '') : ''"
	/>
</template>
<script setup lang="ts">
import { computed, onMounted } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { usePeopleList } from "@/composables/usePeopleList";

const toast = useAppToast();
const props = defineProps<{
	placeholder?: string;
}>();

const modelValue = defineModel<App.Http.Resources.Models.PersonResource[] | null | undefined>();
// USelectMenu's (multiple) v-model requires an array | undefined (no null); callers of this
// wrapper may still pass/receive null to match nullable API fields.
const uiValue = computed<App.Http.Resources.Models.PersonResource[] | undefined>({
	get: () => modelValue.value ?? undefined,
	set: (v) => {
		modelValue.value = v;
	},
});

const { people, load } = usePeopleList();

onMounted(() => {
	load().catch(() => {
		toast.add({
			severity: "error",
			summary: "Error",
			detail: "Failed to fetch persons.",
			life: 3000,
		});
	});
});
</script>
