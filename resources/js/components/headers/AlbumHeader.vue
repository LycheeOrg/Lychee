<template>
	<Toolbar
		v-if="albumStore.album"
		class="w-full border-0 transition-all duration-100 ease-in-out rounded-none"
		:class="{
			'max-h-14': !is_full_screen,
			'max-h-0': is_full_screen,
		}"
	>
		<template #start>
			<GoBack @go-back="emits('goBack')" />
		</template>

		<template #center>
			{{ albumStore.album.title }}
		</template>

		<template #end>
			<Button
				v-if="is_se_enabled && (albumsStore.rootRights?.can_star || albumStore.album.rights?.can_edit)"
				v-tooltip.bottom="$t('gallery.album.show_starred')"
				:icon="albumStore.showStarredOnly ? 'pi pi-star-fill' : 'pi pi-star'"
				:label="String(photosStore.starredPhotosCount)"
				class="border-none hover:text-color"
				severity="secondary"
				text
				@click="emits('showStarredImages')"
			/>
			<Button
				v-if="is_se_enabled && albumStore.album.rights?.can_edit"
				v-tooltip.bottom="$t('gallery.album.copy_starred_names')"
				icon="pi pi-copy"
				class="border-none hover:text-color"
				severity="secondary"
				text
				@click="emits('showSelected')"
			/>
			<router-link v-if="orderManagementStore.hasItems" v-tooltip.bottom="'Basket'" :to="{ name: 'basket' }" class="hidden sm:block">
				<Button
					icon="pi pi-shopping-cart"
					class="border-none"
					:severity="orderManagementStore.order?.status === 'processing' ? 'danger' : 'secondary'"
					text
				/>
			</router-link>
			<router-link
				v-if="is_favourite_enabled && (favourites.photos?.length ?? 0) > 0"
				v-tooltip.bottom="'Favourites'"
				:to="{ name: 'favourites' }"
				class="hidden sm:block"
			>
				<Button icon="pi pi-heart" class="border-none" severity="secondary" text />
			</router-link>
			<Button
				v-if="albumStore.config?.is_search_accessible"
				icon="pi pi-search"
				class="border-none hidden sm:inline-flex"
				severity="secondary"
				text
				@click="emits('openSearch')"
			/>
			<Button v-if="albumStore.rights?.can_upload" icon="pi pi-plus" class="border-none" severity="secondary" text @click="openAddMenu" />
			<template v-if="albumStore.rights?.can_edit">
				<Button
					:icon="is_album_edit_open ? 'pi pi-angle-up' : 'pi pi-angle-down'"
					severity="secondary"
					:class="{ 'ltr:mr-2 rtl:ml-2 border-none': true, 'text-primary-400': is_album_edit_open }"
					text
					@click="emits('toggleEdit')"
				/>
			</template>
		</template>
	</Toolbar>
	<ContextMenu v-if="albumStore.rights?.can_upload" ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ltr:ml-2 rtl:mr-2">
					<!-- @vue-ignore -->
					{{ $t(item.label) }}
				</span>
			</a>
		</template>
	</ContextMenu>
	<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml" class="hidden" @change="uploadTrack" />
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import ContextMenu from "primevue/contextmenu";
import { useContextMenuAlbumAdd } from "@/composables/contextMenus/contextMenuAlbumAdd";
import Divider from "primevue/divider";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useFavouriteStore } from "@/stores/FavouriteState";
import GoBack from "./GoBack.vue";
import { onMounted } from "vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";

const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const favourites = useFavouriteStore();
const albumStore = useAlbumStore();
const orderManagementStore = useOrderManagementStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();

const { dropbox_api_key, is_favourite_enabled, is_se_enabled } = storeToRefs(lycheeStore);
const { is_album_edit_open, is_full_screen } = storeToRefs(togglableStore);

const { toggleCreateAlbum, toggleImportFromLink, toggleImportFromDropbox, toggleUpload, toggleImportFromServer } = useGalleryModals(togglableStore);

const emits = defineEmits<{
	refresh: [];
	toggleEdit: [];
	goBack: [];
	openSearch: [];
	showStarredImages: [];
	showSelected: [];
}>();

function toggleUploadTrack() {
	document.getElementById("upload_track_file")?.click();
}

function uploadTrack(e: Event) {
	if (albumStore.album === undefined) {
		return;
	}

	const target: HTMLInputElement = e.target as HTMLInputElement;
	if (target.files === null) {
		return;
	}
	AlbumService.uploadTrack(albumStore.album.id, target.files[0] as Blob);
}

function deleteTrack() {
	if (albumStore.album === undefined) {
		return;
	}
	AlbumService.deleteTrack(albumStore.album.id);
}

const { addmenu, addMenu, openAddMenu } = useContextMenuAlbumAdd(
	albumStore,
	{
		toggleUpload,
		toggleCreateAlbum,
		toggleImportFromLink,
		toggleUploadTrack,
		deleteTrack,
		toggleImportFromDropbox,
		toggleImportFromServer,
	},
	dropbox_api_key,
);

onMounted(() => {
	lycheeStore.load();
});
</script>
