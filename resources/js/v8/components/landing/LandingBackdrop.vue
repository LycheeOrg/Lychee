<template>
	<div v-if="opacity > 0" class="absolute inset-0 bg-black" :style="{ opacity: opacity / 100 }" />
</template>
<script setup lang="ts">
defineProps<{
	/** 0-100, matches `landing_backdrop_opacity`'s stored range. */
	opacity: number;
}>();
</script>
