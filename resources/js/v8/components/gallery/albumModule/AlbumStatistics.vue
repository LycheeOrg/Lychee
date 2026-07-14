<template>
	<div class="flex gap-4 text-base justify-end text-muted">
		<UTooltip :text="$t('gallery.album.stats.number_of_visits')">
			<span> {{ props.stats.visit_count }} <UIcon name="lucide:eye" class="text-xs ml-1" /> </span>
		</UTooltip>
		<UTooltip :text="$t('gallery.album.stats.number_of_downloads')">
			<span> {{ props.stats.download_count }} <UIcon name="lucide:cloud-download" class="text-xs ml-1" /> </span>
		</UTooltip>
		<UTooltip :text="$t('gallery.album.stats.number_of_shares')">
			<span> {{ props.stats.shared_count }} <UIcon name="lucide:share-2" class="text-xs ml-1" /> </span>
		</UTooltip>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	stats: App.Http.Resources.Models.AlbumStatisticsResource;
}>();
</script>
