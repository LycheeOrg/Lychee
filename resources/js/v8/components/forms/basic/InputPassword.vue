<template>
	<UInput
		v-model="uiValue"
		:class="classValue"
		:disabled="props.disabled"
		:type="show ? 'text' : 'password'"
		:ui="{
			trailing: 'pe-1',
			base: props.invalid ? 'border border-error focus:border-none active:border-none' : '',
		}"
	>
		<template #trailing>
			<UButton
				color="neutral"
				variant="link"
				size="sm"
				:icon="show ? 'lucide:eye-off' : 'lucide:eye'"
				:aria-label="show ? 'Hide password' : 'Show password'"
				@click="
					() => {
						show = !show;
					}
				"
			/>
		</template>
	</UInput>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";

const props = defineProps<{
	disabled?: boolean | undefined;
	invalid?: boolean | undefined;
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
const classValue = computed(() => (props.class ?? "") + " w-full");
const show = ref(false);
</script>

<style lang="css" scoped>
/* Hide the password reveal button in Edge */
::-ms-reveal {
	display: none;
}
</style>
