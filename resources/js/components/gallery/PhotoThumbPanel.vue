<template>
	<Panel id="lychee_view_content" :header="$t(props.header)" class="w-full">
		<template #icons>
			<a class="px-1 cursor-pointer group" @click="(layout.photos_layout = 'square') && activateLayout()" :title="$t('lychee.LAYOUT_SQUARES')">
				<MiniIcon icon="squares" fill="fill-transparent" :class="squareClass" />
			</a>
			<a
				class="px-1 cursor-pointer group"
				@click="(layout.photos_layout = 'justified') && activateLayout()"
				:title="$t('lychee.LAYOUT_JUSTIFIED')"
			>
				<MiniIcon icon="justified" fill="" :class="justifiedClass" />
			</a>
			<a class="px-1 cursor-pointer group" @click="(layout.photos_layout = 'masonry') && activateLayout()" :title="$t('lychee.LAYOUT_MASONRY')">
				<MiniIcon icon="masonry" fill="fill-transparent" :class="masonryClass" />
			</a>
			<a class="px-1 cursor-pointer group" @click="(layout.photos_layout = 'grid') && activateLayout()" :title="$t('lychee.LAYOUT_GRID')">
				<MiniIcon icon="grid" fill="fill-transparent" :class="gridClass" />
			</a>
		</template>
		<div class="relative flex flex-wrap flex-row flex-shrink w-full justify-start align-top" id="photoListing">
			<template v-for="photo in props.photos">
				<PhotoThumb :photo="photo" :album="props.album" :config="props.config" />
			</template>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import PhotoThumb from "@/components/gallery/thumbs/PhotoThumb.vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { onMounted, onUpdated, ref, watch } from "vue";
import { useSquare } from "@/layouts/useSquare";
import { useGrid } from "@/layouts/useGrid";
import { useJustify } from "@/layouts/useJustify";
import { useMasonry } from "@/layouts/useMasonry";

const props = defineProps<{
	header: string;
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource | null;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig | null;
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
}>();

const layout = ref(props.galleryConfig);
const BASE = "my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150 group-hover:stroke-white ";
const squareClass = ref(BASE);
const justifiedClass = ref(BASE);
const masonryClass = ref(BASE);
const gridClass = ref(BASE);

function setClasses() {
	squareClass.value = BASE;
	justifiedClass.value = BASE;
	masonryClass.value = BASE;
	gridClass.value = BASE;

	squareClass.value += layout.value.photos_layout === "square" ? "stroke-primary-400" : "stroke-neutral-400";
	justifiedClass.value +=
		layout.value.photos_layout === "justified" || layout.value.photos_layout === "unjustified" ? "fill-primary-400" : "fill-neutral-400";
	masonryClass.value += layout.value.photos_layout === "masonry" ? "stroke-primary-400" : "stroke-neutral-400";
	gridClass.value += layout.value.photos_layout === "grid" ? "stroke-primary-400" : "stroke-neutral-400";
}

function activateLayout() {
	console.log("activateLayout");
	const photoListing = document.getElementById("photoListing");
	if (photoListing === null) {
		return; // Nothing to do
	}

	switch (layout.value.photos_layout) {
		case "square":
			return useSquare(photoListing, layout.value.photo_layout_square_column_width, layout.value.photo_layout_gap);
		case "justified":
		case "unjustified":
			return useJustify(photoListing, layout.value.photo_layout_justified_row_height);
		case "masonry":
			return useMasonry(photoListing, layout.value.photo_layout_masonry_column_width, layout.value.photo_layout_gap);
		case "grid":
			return useGrid(photoListing, layout.value.photo_layout_grid_column_width, layout.value.photo_layout_gap);
	}
}

setClasses();

onMounted(() => {
	activateLayout();
});

onUpdated(() => {
	activateLayout();
});
</script>
