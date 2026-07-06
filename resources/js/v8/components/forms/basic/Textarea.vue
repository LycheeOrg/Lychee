<template>
	<UTextarea v-model="uiValue" :class="classValueComputed" :disabled="props.invalid" :rows="props.rows" :placeholder="props.placeholder" />
</template>
<script setup lang="ts">
import { useLtRorRtL } from "@/utils/Helpers";
import { computed } from "vue";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	autoResize?: boolean | undefined;
	invalid?: boolean | undefined;
	variant?: "outlined" | "filled" | undefined;
	fluid?: boolean;
	class?: string;
	rows?: number;
	cols?: number;
	placeholder?: string;
}>();

const modelValue = defineModel<string | null | undefined>();
// UTextarea's v-model requires `string | undefined` (no null); callers of this wrapper may
// still pass/receive null to match nullable API fields.
const uiValue = computed<string | undefined>({
	get: () => modelValue.value ?? undefined,
	set: (v) => {
		modelValue.value = v;
	},
});
const classValueComputed = computed(() => {
	return (
		(props.class ?? "") +
		(props.fluid ? " w-full" : "") +
		" p-3 w-full border-t-transparent border-b hover:border-b-primary focus:border-b-primary" +
		(isLTR() ? " border-r-transparent border-l" : " border-l-transparent border-r") +
		(isLTR() ? " hover:border-l-primary focus:border-l-primary" : " hover:border-r-primary focus:border-r-primary")
	);
});
</script>
