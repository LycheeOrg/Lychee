<template>
	<div class="image-container overflow-hidden absolute block top-0 left-0 w-full h-full object-cover object-center z-0">
		<img :src="src" class="w-full h-full object-cover select-none" :style="{ objectPosition: objectPosition }" draggable="false" />
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	src: string;
	focusX?: number | null;
	focusY?: number | null;
}>();

const objectPosition = computed(() => {
	if (props.focusX === undefined || props.focusX === null || props.focusY === undefined || props.focusY === null) {
		return "center center";
	}
	const x = (props.focusX * -1 + 1) * 50;
	const y = (props.focusY * -1 + 1) * 50;
	return `${x}% ${y}%`;
});
</script>
