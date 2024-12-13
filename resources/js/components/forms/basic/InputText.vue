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
		:ptOptions="props.ptOptions"
		:unstyled="props.unstyled"
		:autofocus="props.autofocus"
		@update:modelValue="($event) => emits('updated', $event)"
	/>
</template>
<script setup lang="ts">
import { ref } from "vue";
import InputText, { InputTextPassThroughOptions } from "primevue/inputtext";
import type { PassThroughOptions } from "primevue/passthrough";
import type { DesignToken, Nullable, PassThrough } from "@primevue/core";

const props = withDefaults(
	defineProps<{
		size?: "small" | "large" | undefined;
		invalid?: boolean | undefined;
		variant?: "outlined" | "filled" | undefined;
		fluid?: boolean;
		dt?: DesignToken<any>;
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
const classValue = ref((props.class ?? "") + " border-0 p-3 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400");
</script>
