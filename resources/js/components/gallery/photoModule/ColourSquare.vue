<template>
	<div
		v-tooltip.bottom="props.colour"
		class="aspect h-6 w-6 border-surface-700 border"
		:style="`background-color: ${props.colour}`"
		@click="copyToClipboard"
	></div>
</template>
<script setup lang="ts">
import { useToast } from "primevue/usetoast";

const props = defineProps<{ colour: string }>();
const toast = useToast();

function copyToClipboard() {
	navigator.clipboard.writeText(props.colour).then(() => toast.add({ severity: "info", summary: props.colour, life: 3000 }));
}
</script>
