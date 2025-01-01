<template>
	<div class="flex justify-between hover:bg-primary-emphasis/5 gap-8 items-center md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mx-auto">
		<div class="w-1/4 flex items-center just">
			<span class="w-full inline-block whitespace-nowrap text-ellipsis overflow-hidden">
				{{ props.duplicate.album_title }}
			</span>
			<router-link :to="{ name: 'album', params: { albumid: props.duplicate.album_id } }" target="_blank" class="">
				<i class="pi pi-link hover:text-primary-emphasis"></i>
			</router-link>
		</div>
		<div class="w-1/4 flex gap-2">
			<span
				class="w-full inline-block whitespace-nowrap text-ellipsis overflow-hidden"
				v-tooltip="{ value: '<img src=' + props.duplicate.url + ' />', escape: false, pt: { arrow: { class: 'hidden' } } }"
			>
				{{ props.duplicate.photo_title }}
			</span>
			<router-link :to="{ name: 'album', params: { albumid: props.duplicate.album_id } }" target="_blank" class="">
				<i class="pi pi-link hover:text-primary-emphasis"></i>
			</router-link>
		</div>
		<div class="w-1/4 font-mono text-xs">{{ props.duplicate.checksum.slice(12) }}</div>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	duplicate: App.Http.Resources.Models.Duplicates.Duplicate;
}>();
</script>
