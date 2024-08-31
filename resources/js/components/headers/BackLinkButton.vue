<template>
	<Button as="a" :href="props.config.back_button_url" text class="p-button-secondary">
		<!-- v-if="props.config.back_button_enabled" -->
		{{ props.config.back_button_text }}
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
