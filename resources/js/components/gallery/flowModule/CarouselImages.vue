<template>
	<div class="w-full bg-surface-800 overflow-x-scroll flex pt-1 gap-1">
		<div v-for="(photo, idx) in photos" :key="`album-${props.albumId}-photo-${photo.id}`" class="block shrink-0 grow-1 relative">
			<img
				:src="photo.size_variants.thumb?.url ?? '/img/no_images.svg'"
				:alt="photo.title"
				class="h-(--carousel-height) w-full object-cover"
				@click="emits('clicked', idx + 1)"
			/>
			<Blur v-if="props.isNsfw" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import Blur from "./Blur.vue";

const props = defineProps<{
	dropFirst: boolean;
	photos: App.Http.Resources.Models.PhotoResource[];
	albumId: string;
	isNsfw: boolean;
}>();

const emits = defineEmits<{
	clicked: [idx: number];
}>();

const photos = computed(() => {
	if (props.dropFirst) {
		return props.photos.slice(1);
	}
	return props.photos;
});
</script>
