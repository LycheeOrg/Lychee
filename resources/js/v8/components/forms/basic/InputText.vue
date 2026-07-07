<template>
	<UInput
		v-model="uiValue"
		:class="classValue"
		:disabled="props.invalid"
		:autofocus="props.autofocus"
		@update:model-value="($event: string) => emits('updated', $event)"
	/>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";

const props = withDefaults(
	defineProps<{
		invalid?: boolean | undefined;
		class?: string;
		autofocus?: boolean;
	}>(),
	{
		autofocus: false,
	},
);

const emits = defineEmits(["updated"]);
const modelValue = defineModel<string | null | undefined>();
// UInput's v-model requires `string | undefined` (no null); callers of this wrapper may
// still pass/receive null to match nullable API fields.
const uiValue = computed<string | undefined>({
	get: () => modelValue.value ?? undefined,
	set: (v) => {
		modelValue.value = v;
	},
});
const classValue = ref((props.class ?? "") + " w-full");
</script>
