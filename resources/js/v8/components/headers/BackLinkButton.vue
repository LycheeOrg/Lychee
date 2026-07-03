<template>
	<UButton
		as="a"
		:href="props.config.back_button_url"
		color="neutral"
		variant="ghost"
		:class="props.config.back_button_enabled ? '' : 'hidden'"
	>
		<UIcon name="prime:home" class="lg:hidden" />
		<span class="hidden lg:inline">{{ props.config.back_button_text }}</span>
	</UButton>
</template>
<script setup lang="ts">
const props = defineProps<{
	config: {
		back_button_enabled: boolean;
		back_button_text: string;
		back_button_url: string;
	};
}>();
</script>
