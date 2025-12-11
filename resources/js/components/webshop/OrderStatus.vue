<template>
	<Tag
		:severity="
			{
				pending: 'info',
				paid: 'success',
				offline: 'contrast',
				completed: 'primary',
				processing: 'warn',
				failed: 'danger',
				closed: 'success',
			}[props.status as string] || 'secondary'
		"
		:class="{ 'opacity-50': props.status === 'pending' }"
		:style="{
			fontSize: props.size === 'small' ? '0.75rem' : '0.875rem',
			padding: props.size === 'small' ? '0.125rem 0.25rem' : '0.25rem 0.5rem',
			'border-radius': props.size === 'small' ? '0.5rem' : '1rem',
		}"
	>
		{{ trans(`webshop.status.${status}`) }}
	</Tag>
</template>
<script setup lang="ts">
import Tag from "primevue/tag";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	status: App.Enum.PaymentStatusType;
	size?: "small" | "normal";
}>();
</script>
