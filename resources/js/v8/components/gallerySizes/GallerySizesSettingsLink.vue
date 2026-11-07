<template>
	<div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-md bg-elevated/50 px-3 py-2">
		<span class="text-sm text-muted">{{ $t("gallery_sizes.settings_link_details") }}</span>
		<UButton
			:to="{ name: 'gallery-sizes' }"
			:label="$t('gallery_sizes.settings_link')"
			icon="lucide:ruler"
			size="sm"
			color="primary"
			variant="soft"
		/>
	</div>
</template>
<script setup lang="ts">
/**
 * Feature 110 (FR-110-04): link from Settings › Gallery (category tab and "all"
 * view) to the gallery sizes preview page.
 */
</script>
