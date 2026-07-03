<template>
	<UTooltip :text="props.colour">
		<div class="aspect h-6 w-6 border-default border" :style="`background-color: ${props.colour}`" @click="copyToClipboard"></div>
	</UTooltip>
</template>
<script setup lang="ts">
import { useAppToast } from "@/v8/composables/useAppToast";

const props = defineProps<{ colour: string }>();
const toast = useAppToast();

function copyToClipboard() {
	navigator.clipboard.writeText(props.colour).then(() => toast.add({ severity: "info", summary: props.colour, life: 3000 }));
}
</script>
