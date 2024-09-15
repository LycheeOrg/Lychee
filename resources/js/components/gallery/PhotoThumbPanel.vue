<template>
	<Panel id="lychee_view_content" :header="$t(props.header)" class="w-full border-0">
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
	<ContextMenu v-if="props.album?.rights.can_edit" ref="singlephotomenu" :model="singlePhotoMenu">
		<template #item="{ item, props }">
			<a v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
</template>
<script setup lang="ts">
import { onMounted, onUpdated } from "vue";
import Panel from "primevue/panel";
import PhotoThumb from "@/components/gallery/thumbs/PhotoThumb.vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ContextMenu from "primevue/contextmenu";
import { useContextMenuPhoto } from "@/composables/contextMenus/contextMenuPhoto";
import { useLayouts } from "@/layouts/PhotoLayout";

const props = defineProps<{
	header: string;
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource | null;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig | null;
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
}>();

const { singlephotomenu, singlePhotoMenu } = useContextMenuPhoto();

const { activateLayout, layout, squareClass, justifiedClass, masonryClass, gridClass } = useLayouts(props.galleryConfig);

onMounted(() => {
	activateLayout();
});

onUpdated(() => {
	activateLayout();
});
</script>
