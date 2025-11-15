<template>
	<Tag
		:severity="
			{
				pending: 'info',
				paid: 'success',
				completed: 'success',
				failed: 'danger',
			}[props.status] || 'secondary'
		"
		rounded
	>
		{{ status }}
	</Tag>
</template>
<script setup lang="ts">
import Tag from "primevue/tag";

const props = defineProps<{
	status: string;
}>();
</script>
