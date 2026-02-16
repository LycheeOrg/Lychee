<template>
	<div class="image-container overflow-hidden absolute block top-0 left-0 w-full h-full object-cover object-center z-0">
		<img :src="src" class="w-full h-full object-cover select-none" draggable="false" />
	</div>
</template>

<script setup lang="ts">
const props = defineProps<{
	src: string;
}>();
</script>
