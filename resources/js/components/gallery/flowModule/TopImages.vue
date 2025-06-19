<template>
	<div
		:class="{
			'w-full bg-surface-800 flex h-(--header-height) gap-1': true,
			'flex-wrap': !isShort,
		}"
	>
		<div
			v-for="(photo, idx) in props.photos.slice(0, 5)"
			:key="`album-${props.albumId}-photo-${photo.id}`"
			:class="{
				'relative shrink-1 grow-1 flex-1/4 h-(--top-images-height)': !isShort,
				'h-(--header-height)': isShort,
			}"
		>
			<img
				:src="photo.size_variants.small?.url ?? '/img/no_images.svg'"
				:alt="photo.title"
				class="object-cover h-full w-full"
				@click="emits('clicked', idx)"
			/>
			<Blur v-if="props.isNsfw" />
		</div>
		<div v-if="props.photos.length > 5" class="aspect-square flex items-center justify-center px-16 text-muted-color text-2xl">
			<i class="pi pi-plus mx-2" />
			{{ props.photos.length - 5 }}
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import Blur from "./Blur.vue";

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	albumId: string;
	isNsfw: boolean;
}>();

const isShort = computed(() => {
	return props.photos.length < 4;
});

const emits = defineEmits<{
	clicked: [idx: number];
}>();
</script>
