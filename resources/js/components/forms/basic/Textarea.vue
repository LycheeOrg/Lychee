<template>
	<Textarea
		v-model="modelValue"
		:class="classValue"
		:autoResize="props.autoResize"
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
import { ref } from "vue";
import Textarea, { TextareaPassThroughOptions } from "primevue/textarea";
import type { PassThroughOptions } from "primevue/passthrough";
import type { DesignToken, Nullable, PassThrough } from "@primevue/core";

const props = defineProps<{
	autoResize?: boolean | undefined;
	invalid?: boolean | undefined;
	variant?: "outlined" | "filled" | undefined;
	fluid?: boolean;
	dt?: DesignToken<any>;
	pt?: PassThrough<TextareaPassThroughOptions>;
	ptOptions?: PassThroughOptions;
	unstyled?: boolean;
	class?: string;
}>();

const modelValue = defineModel<Nullable<string>>();
const classValue = ref(
	(props.class ?? "") +
		" p-3 w-full border-t-transparent border-r-transparent border-b border-l hover:border-b-primary-400 hover:border-l-primary-400 focus:border-b-primary-400 focus:border-l-primary-400",
);
</script>
