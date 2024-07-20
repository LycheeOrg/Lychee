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
	/>
</template>
<script setup lang="ts">
import InputText, { InputTextPassThroughOptions } from "primevue/inputtext";
import type { PassThroughOptions } from "primevue/passthrough";
import type { DesignToken, PassThrough } from "@primevue/core";
import { ref } from "vue";

const props = defineProps<{
	size?: "small" | "large" | undefined;
	invalid?: boolean | undefined;
	variant?: "outlined" | "filled" | undefined;
	fluid?: boolean;
	dt?: DesignToken<any>;
	pt?: PassThrough<InputTextPassThroughOptions>;
	ptOptions?: PassThroughOptions;
	unstyled?: boolean;
	class?: string;
}>();

const modelValue = defineModel();
const classValue = ref((props.class ?? "") + " p-3 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400");
</script>
