<template>
	<Textarea
		v-model="modelValue"
		:class="classValueComputed"
		:auto-resize="props.autoResize"
		:invalid="props.invalid"
		:variant="props.variant"
		:fluid="props.fluid"
		:dt="props.dt"
		:pt="props.pt"
		:rows="props.rows"
		:cols="props.cols"
		:pt-options="props.ptOptions"
		:unstyled="props.unstyled"
		:placeholder="props.placeholder"
	/>
</template>
<script setup lang="ts">
import Textarea, { TextareaPassThroughOptions } from "primevue/textarea";
import type { PassThroughOptions } from "primevue/passthrough";
import type { DesignToken, Nullable, PassThrough } from "@primevue/core";
import { useLtRorRtL } from "@/utils/Helpers";
import { computed } from "vue";
import { TextareaDesignTokens } from "@primeuix/themes/types/textarea";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	autoResize?: boolean | undefined;
	invalid?: boolean | undefined;
	variant?: "outlined" | "filled" | undefined;
	fluid?: boolean;
	dt?: DesignToken<TextareaDesignTokens>;
	pt?: PassThrough<TextareaPassThroughOptions>;
	ptOptions?: PassThroughOptions;
	unstyled?: boolean;
	class?: string;
	rows?: number;
	cols?: number;
	placeholder?: string;
}>();

const modelValue = defineModel<Nullable<string>>();
const classValueComputed = computed(() => {
	return (
		(props.class ?? "") +
		(props.fluid ? " w-full" : "") +
		" p-3 w-full border-t-transparent border-b hover:border-b-primary-400  focus:border-b-primary-400" +
		(isLTR() ? " border-r-transparent border-l" : " border-l-transparent border-r") +
		(isLTR() ? " hover:border-l-primary-400 focus:border-l-primary-400" : " hover:border-r-primary-400 focus:border-r-primary-400")
	);
});
</script>
