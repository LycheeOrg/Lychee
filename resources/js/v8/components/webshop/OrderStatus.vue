<template>
	<UBadge
		:color="
			(
				{
					pending: 'info',
					paid: 'success',
					offline: 'neutral',
					completed: 'primary',
					processing: 'warning',
					failed: 'error',
					closed: 'success',
				} as Record<string, 'info' | 'success' | 'neutral' | 'primary' | 'warning' | 'error'>
			)[props.status] || 'neutral'
		"
		:class="{ 'opacity-50': props.status === 'pending' }"
		:size="props.size === 'small' ? 'sm' : 'md'"
	>
		{{ trans(`webshop.status.${status}`) }}
	</UBadge>
</template>
<script setup lang="ts">
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	status: App.Enum.PaymentStatusType;
	size?: "small" | "normal";
}>();
</script>
