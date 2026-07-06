<template>
	<UInput v-model="uiValue" :class="classValue" :disabled="props.invalid" :type="show ? 'text' : 'password'" :ui="{ trailing: 'pe-1' }">
		<template #trailing>
			<UButton
				color="neutral"
				variant="link"
				size="sm"
				:icon="show ? 'prime:eye-slash' : 'prime:eye'"
				:aria-label="show ? 'Hide password' : 'Show password'"
				@click="show = !show"
			/>
		</template>
	</UInput>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";

const props = defineProps<{
	size?: "small" | "large" | undefined;
	invalid?: boolean | undefined;
	variant?: "outlined" | "filled" | undefined;
	fluid?: boolean;
	class?: string;
}>();

const modelValue = defineModel<string | null | undefined>();
// UInput's v-model requires `string | undefined` (no null); callers of this wrapper may
// still pass/receive null to match nullable API fields.
const uiValue = computed<string | undefined>({
	get: () => modelValue.value ?? undefined,
	set: (v) => {
		modelValue.value = v;
	},
});
const classValue = ref((props.class ?? "") + " border-0 w-full border-b hover:border-b-error focus:border-b-error");
const show = ref(false);
</script>
