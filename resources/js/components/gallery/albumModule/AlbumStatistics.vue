<template>
	<div class="flex gap-4 text-base justify-end text-muted-color">
		<span v-tooltip.bottom="{ value: $t('gallery.album.stats.number_of_visits') }">
			{{ props.stats.visit_count }} <i class="pi pi-eye text-xs ml-1" />
		</span>
		<span v-tooltip.bottom="{ value: $t('gallery.album.stats.number_of_downloads') }">
			{{ props.stats.download_count }} <i class="pi pi-cloud-download text-xs ml-1" />
		</span>
		<span v-tooltip.bottom="{ value: $t('gallery.album.stats.number_of_shares') }">
			{{ props.stats.shared_count }} <i class="pi pi-share-alt text-xs ml-1" />
		</span>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	stats: App.Http.Resources.Models.AlbumStatisticsResource;
}>();
</script>
