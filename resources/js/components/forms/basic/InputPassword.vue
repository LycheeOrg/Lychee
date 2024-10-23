<template>
	<InputText
		v-model="modelValue"
		:class="classValue"
		:invalid="props.invalid"
		:variant="props.variant"
		:fluid="props.fluid"
		:dt="props.dt"
		:pt="props.pt"
		:ptOptions="props.ptOptions"
		:unstyled="props.unstyled"
		type="password"
	/>
</template>
<script setup lang="ts">
import { ref } from "vue";
import InputText, { InputTextPassThroughOptions } from "primevue/inputtext";
import type { PassThroughOptions } from "primevue/passthrough";
import type { DesignToken, Nullable, PassThrough } from "@primevue/core";

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

const modelValue = defineModel<Nullable<string>>();
const classValue = ref((props.class ?? "") + " border-0 p-3 w-full border-b hover:border-b-danger-600 focus:border-b-danger-600");
</script>
