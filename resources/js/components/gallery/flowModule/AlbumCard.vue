<template>
	<div
		class="w-full md:w-2xl rounded-2xl overflow-hidden"
		:style="{
			'--header-height': props.config.image_header_height + 'rem',
			'--carousel-height': props.config.carousel_height + 'rem',
			'--top-images-height': props.config.image_header_height / 2 + 'rem',
		}"
	>
		<HeaderImage
			v-if="header"
			:header="header"
			:image_header_cover="props.config.image_header_cover"
			:title="props.album.title"
			:is-nsfw="props.album.is_nsfw"
		/>
		<TopImages v-else :photos="props.album.photos" :albumId="props.album.id" :is-nsfw="props.album.is_nsfw" />
		<CarouselImages
			v-if="props.config.is_image_header_enabled && props.config.is_carousel_enabled"
			:album-id="props.album.id"
			:photos="props.album.photos"
			:drop-first="props.config.is_highlight_first_picture"
			:is-nsfw="props.album.is_nsfw"
		/>
		<div class="p-6 bg-gradient-to-b from-surface-800 to-surface-800">
			<h3 class="text-xl font-semibold text-surface-0">{{ props.album.title }}</h3>
			<p class="text-sm text-muted-color">{{ props.album.published_created_at }}</p>
			<p class="text-sm text-muted-color" v-if="props.album.min_max_text">{{ props.album.min_max_text }}</p>
			<p class="text-sm text-muted-color prose dark:prose-invert my-4" v-if="props.album.description" v-html="props.album.description"></p>
			<div class="flex justify-between items-end flex-row-reverse">
				<Button
					as="a"
					:href="router.resolve({ name: 'album', params: { albumId: props.album.id } }).href"
					severity="contrast"
					class="border-none font-bold"
					v-if="props.config.is_display_open_album_button"
				>
					{{ "Open album" }} <i class="pi pi-angle-double-right" />
				</Button>
				<AlbumStatistics v-if="props.album.statistics" :stats="props.album.statistics" />
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import { computed } from "vue";
import { useRouter } from "vue-router";
import HeaderImage from "./HeaderImage.vue";
import TopImages from "./TopImages.vue";
import CarouselImages from "./CarouselImages.vue";
import AlbumStatistics from "../albumModule/AlbumStatistics.vue";

const router = useRouter();

const props = defineProps<{
	album: App.Http.Resources.Flow.FlowItemResource;
	config: App.Http.Resources.Flow.InitResource;
}>();

const header = computed<App.Http.Resources.Models.SizeVariantsResouce | undefined>(() => {
	if (!props.config.is_image_header_enabled) {
		return undefined;
	}

	let photo: App.Http.Resources.Models.SizeVariantsResouce | null = null;
	if (!props.config.is_highlight_first_picture) {
		photo = props.album.cover;
	}

	return photo ?? props.album.photos[0]?.size_variants ?? undefined;
});
</script>
