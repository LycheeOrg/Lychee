<template>
	<InputText
		v-model="modelValue"
		:class="classValue"
		:size="props.size"
		:invalid="props.invalid"
		:variant="props.variant"
		:fluid="props.fluid"
		:dt="props.dt"
		:pt="props.pt"
		:pt-options="props.ptOptions"
		:unstyled="props.unstyled"
		:autofocus="props.autofocus"
		@update:model-value="($event) => emits('updated', $event)"
	/>
</template>
<script setup lang="ts">
import { ref } from "vue";
import InputText, { InputTextPassThroughOptions } from "primevue/inputtext";
import type { PassThroughOptions } from "primevue/passthrough";
import type { DesignToken, Nullable, PassThrough } from "@primevue/core";
import { InputTextDesignTokens } from "@primeuix/themes/types/inputtext";

const props = withDefaults(
	defineProps<{
		size?: "small" | "large" | undefined;
		invalid?: boolean | undefined;
		variant?: "outlined" | "filled" | undefined;
		fluid?: boolean;
		dt?: DesignToken<InputTextDesignTokens>;
		pt?: PassThrough<InputTextPassThroughOptions>;
		ptOptions?: PassThroughOptions;
		unstyled?: boolean;
		class?: string;
		autofocus?: boolean;
	}>(),
	{
		autofocus: false,
	},
);

const emits = defineEmits(["updated"]);
const modelValue = defineModel<Nullable<string>>();
const classValue = ref((props.class ?? "") + " border-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400");
</script>
