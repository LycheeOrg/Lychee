<template>
	<UInput
		v-model="uiValue"
		v-bind="$attrs"
		:class="classValue"
		:disabled="props.disabled"
		:type="show ? 'text' : 'password'"
		:ui="{
			trailing: 'pe-1',
			base: props.invalid ? 'border border-error focus:border-none active:border-none' : '',
		}"
	>
		<template #trailing>
			<template v-if="props.hasCheck && result && result.score < 4 && (result.feedback.warning || result.feedback.suggestions.length > 0)">
				<UTooltip arrow text="Password strength">
					<template #content>
						<p v-if="result.feedback.warning" class="text-sm font-medium text-error">{{ result.feedback.warning }}</p>
						<ul v-else>
							<li v-for="suggestion in result.feedback.suggestions" :key="`${suggestion}`" class="text-sm font-medium">
								{{ suggestion }}
							</li>
						</ul>
					</template>
					<UIcon
						name="lucide:triangle-alert"
						tabindex="-1"
						:class="{
							'inline-block size-4': true,
							'text-error': result.feedback.warning,
							'text-warning': result.feedback.suggestions.length > 0 && !result.feedback.warning,
						}"
					/>
				</UTooltip>
			</template>
			<UButton
				color="neutral"
				variant="link"
				size="sm"
				tabindex="-1"
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
	<UProgress v-if="hasCheck" :color="color" :indicator="text" :model-value="strength" :max="5" size="sm" />
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { usePasswordStrength } from "@/v8/composables/usePasswordStrength";

const props = defineProps<{
	disabled?: boolean | undefined;
	invalid?: boolean | undefined;
	class?: string;
	hasCheck?: boolean | undefined;
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

const { color, text, strength, result } = usePasswordStrength(modelValue);
</script>

<style lang="css" scoped>
/* Hide the password reveal button in Edge */
::-ms-reveal {
	display: none;
}
</style>
