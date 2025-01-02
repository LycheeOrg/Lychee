<template>
	<div class="hover:bg-primary-emphasis/5">
	<template v-for="duplicate in props.duplicates.data">
		<div class="flex justify-between hover:text-color-emphasis gap-8 items-center" @mouseover="hover(duplicate.url??'', duplicate.photo_title)">
			<div class="w-1/4 flex items-center gap-2 group">
				<router-link :to="{ name: 'album', params: { albumid: duplicate.album_id } }" target="_blank" class="">
					<i class="pi pi-link text-primary-emphasis hover:text-primary-emphasis-alt"></i>
				</router-link>
				<span class="w-full inline-block whitespace-nowrap text-ellipsis overflow-hidden">
					{{ duplicate.album_title }}
				</span>
			</div>
			<div class="w-1/4 flex gap-2 group">
				<router-link :to="{ name: 'album', params: { albumid: duplicate.album_id } }" target="_blank" class="">
					<i class="pi pi-link text-primary-emphasis hover:text-primary-emphasis-alt"></i>
				</router-link>
				<span class="w-full inline-block whitespace-nowrap text-ellipsis overflow-hidden">
					{{ duplicate.photo_title }}
				</span>
			</div>
			<div class="w-1/4 font-mono text-xs">{{ duplicate.checksum.slice(0, 12) }}</div>
		</div>
	</template>
	</div>
</template>
<script setup lang="ts">
import { type SplitData } from '@/composables/album/splitter';

const props = defineProps<{
	duplicates: SplitData<App.Http.Resources.Models.Duplicates.Duplicate>;
}>();

const emits = defineEmits<{
	hover: [src: string, title: string];
}>();

function hover(url: string, title: string) {
	emits("hover", url, title);
}
</script>
