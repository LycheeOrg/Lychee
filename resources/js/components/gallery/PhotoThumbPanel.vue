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
			<template v-for="(photo, idx) in props.photos">
				<PhotoThumb
					@click="maySelect(idx, $event)"
					@contextmenu.prevent="menuOpen(idx, $event)"
					:is-selected="isPhotoSelected(idx)"
					:photo="photo"
					:album="props.album"
					:config="props.config"
				/>
			</template>
		</div>
	</Panel>
	<ContextMenu v-if="props.album?.rights.can_edit" ref="photomenu" :model="PhotoMenu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
	<ContextMenu v-if="props.album?.rights.can_edit" ref="photomenu" :model="PhotoMenu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
	<DialogPhotoMove v-model:visible="isMovePhotoVisible" :photo="photo" :photo-ids="getSelectedPhotosIds()" />
	<DialogPhotoDelete v-model:visible="isDeletePhotoVisible" :photo="photo" :photo-ids="getSelectedPhotosIds()" />
</template>
<script setup lang="ts">
import { computed, onMounted, onUpdated } from "vue";
import Panel from "primevue/panel";
import PhotoThumb from "@/components/gallery/thumbs/PhotoThumb.vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ContextMenu from "primevue/contextmenu";
import { useContextMenuPhoto } from "@/composables/contextMenus/contextMenuPhoto";
import { useLayouts } from "@/layouts/PhotoLayout";
import Divider from "primevue/divider";
import { usePhotosSelection } from "@/composables/selections/photoSelections";
import PhotoService from "@/services/photo-service";
import DialogPhotoMove from "../forms/photo/DialogPhotoMove.vue";
import { useMovePhotoOpen } from "@/composables/modalsTriggers/movePhotoOpen";
import { useDeletePhotoOpen } from "@/composables/modalsTriggers/deletePhotoOpen";
import DialogPhotoDelete from "../forms/photo/DialogPhotoDelete.vue";

const props = defineProps<{
	header: string;
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource | null;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
}>();

const { isMovePhotoVisible, toggleMovePhoto } = useMovePhotoOpen();
const { isDeletePhotoVisible, toggleDeletePhoto } = useDeletePhotoOpen();

const { getAlbum, selectedPhotos, isPhotoSelected, getSelectedPhotos, getSelectedPhotosIds, addToPhotoSelection, maySelect } =
	usePhotosSelection(props);

const photo = computed(() => (getSelectedPhotos().length === 1 ? getSelectedPhotos()[0] : undefined));

function menuOpen(idx: number, e: MouseEvent): void {
	if (!isPhotoSelected(idx)) {
		selectedPhotos.value = [];
		addToPhotoSelection(idx);
	}
	photomenu.value.show(e);
}

const { photomenu, PhotoMenu } = useContextMenuPhoto(
	{
		getAlbum,
		getSelectedPhotos,
	},
	{
		star: () => PhotoService.star(getSelectedPhotosIds(), true),
		unstar: () => PhotoService.star(getSelectedPhotosIds(), false),
		setAsCover: () => {},
		setAsHeader: () => {},
		toggleTag: () => {},
		toggleRename: () => {},
		toggleCopyTo: () => {},
		toggleMove: toggleMovePhoto,
		toggleDelete: toggleDeletePhoto,
		toggleDownload: () => {},
	},
);

// Layouts stuff
const { activateLayout, layout, squareClass, justifiedClass, masonryClass, gridClass } = useLayouts(props.galleryConfig);
onMounted(() => activateLayout());
onUpdated(() => activateLayout());
</script>
