<template></template>
<script setup lang="ts">
import { watch } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();

watch(
	() => route.params.id,
	(newId, oldId) => {
		// react to route changes...
	},
);
</script>
