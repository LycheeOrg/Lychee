<template>
	<Button
		as="a"
		:href="props.config.back_button_url"
		severity="secondary"
		text
		class="border-none"
		:class="props.config.back_button_enabled ? '' : 'hidden'"
	>
		<i class="pi pi-home sm:hidden" />
		<span class="hidden sm:inline">{{ props.config.back_button_text }}</span>
	</Button>
</template>
<script setup lang="ts">
import Button from "primevue/button";

const props = defineProps<{
	config: {
		back_button_enabled: boolean;
		back_button_text: string;
		back_button_url: string;
	};
}>();
</script>
