<template>
	<Panel id="lychee_view_content" :header="$t(props.header)" class="w-full border-0">
		<template #icons>
			<PhotoThumbPanelControl v-model:layout="layout" />
		</template>
		<PhotoThumbPanelList
			:photos="props.photos"
			:layout="layout"
			:album="props.album"
			:galleryConfig="props.galleryConfig"
			:selectedPhotos="props.selectedPhotos"
			:iter="0"
			@clicked="propagateClicked"
			@contexted="propagateMenuOpen"
		/>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Panel from "primevue/panel";
import PhotoThumbPanelList from "./PhotoThumbPanelList.vue";
import PhotoThumbPanelControl from "./PhotoThumbPanelControl.vue";

const props = defineProps<{
	header: string;
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	photoLayout: App.Enum.PhotoLayoutType;
	album:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined;
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	selectedPhotos: string[];
}>();

const layout = ref(props.photoLayout);

// bubble up.
const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
}>();

const propagateClicked = (idx: number, e: MouseEvent) => {
	emits("clicked", idx, e);
};

const propagateMenuOpen = (idx: number, e: MouseEvent) => {
	emits("contexted", idx, e);
};
</script>
