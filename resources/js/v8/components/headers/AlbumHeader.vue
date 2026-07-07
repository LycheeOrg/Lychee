<template>
	<UHeader
		v-if="albumStore.album"
		:class="{
			'max-h-14': !is_full_screen,
			'max-h-0': is_full_screen,
		}"
		:toggle="false"
	>
		<template #title>
			<LycheeBreadcrumb
				v-if="showBreadcrumb"
				:key="`albumheader-${albumStore.albumId}`"
				:items="albumStore.modelAlbum?.breadcrumb ?? []"
				:current-title="albumStore.album?.title ?? ''"
				@go-back="emits('goBack')"
			/>
			<GoBack v-else @go-back="emits('goBack')" />
		</template>

		<span v-if="!showBreadcrumb">{{ albumStore.album.title }}</span>

		<template #right>
			<UButton
				v-if="is_touch_select_mode && (selectedPhotosIds.length > 0 || selectedAlbumsIds.length > 0)"
				icon="prime:ellipsis-v"
				color="neutral"
				variant="ghost"
				@click="(e: MouseEvent) => emits('openContextMenu', e)"
			/>
			<UButton
				v-if="isTouchDevice() && canInteractPhoto()"
				:icon="is_touch_select_mode ? 'prime:check-square' : 'prime:stop'"
				color="neutral"
				variant="ghost"
				:class="{ 'text-primary': is_touch_select_mode }"
				@click="togglableStore.toggleTouchSelectMode()"
			/>
			<template v-if="!is_touch_select_mode">
				<UButton
					v-if="is_se_enabled && albumStore.album.rights?.can_edit"
					icon="prime:copy"
					color="neutral"
					variant="ghost"
					class="hover:text-default"
					@click="emits('showSelected')"
				/>
				<RouterLink v-if="orderManagementStore.hasItems" :to="{ name: 'basket' }" class="hidden sm:block">
					<UButton
						icon="prime:shopping-cart"
						:color="orderManagementStore.order?.status === 'processing' ? 'error' : 'neutral'"
						variant="ghost"
					/>
				</RouterLink>
				<RouterLink v-if="is_favourite_enabled && (favourites.photos?.length ?? 0) > 0" :to="{ name: 'favourites' }" class="hidden sm:block">
					<UButton icon="prime:heart" color="neutral" variant="ghost" />
				</RouterLink>
				<UButton
					v-if="albumStore.config?.is_search_accessible"
					icon="prime:search"
					color="neutral"
					variant="ghost"
					class="hidden sm:inline-flex"
					@click="emits('openSearch')"
				/>
				<UDropdownMenu v-if="albumStore.rights?.can_upload" :items="addMenuSections">
					<UButton icon="prime:plus" color="neutral" variant="ghost" />
				</UDropdownMenu>
				<template v-if="albumStore.rights?.can_edit">
					<UButton
						:icon="is_album_edit_open ? 'prime:angle-up' : 'prime:angle-down'"
						color="neutral"
						variant="ghost"
						:class="{ 'ltr:mr-2 rtl:ml-2': true, 'text-primary': is_album_edit_open }"
						@click="emits('toggleEdit')"
					/>
				</template>
			</template>
		</template>
	</UHeader>
	<input id="upload_track_file" type="file" name="fileElem" accept="application/x-gpx+xml" class="hidden" @change="uploadTrack" />
</template>
<script setup lang="ts">
import LycheeBreadcrumb from "./LycheeBreadcrumb.vue";
import { useContextMenuAlbumAdd, type AddMenuItem } from "@/composables/contextMenus/contextMenuAlbumAdd";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useFavouriteStore } from "@/stores/FavouriteState";
import GoBack from "./GoBack.vue";
import { computed, onMounted } from "vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useAlbumActions } from "@/composables/album/albumActions";
import { trans } from "laravel-vue-i18n";
import type { DropdownMenuItem } from "@nuxt/ui";

const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const favourites = useFavouriteStore();
const albumStore = useAlbumStore();
const orderManagementStore = useOrderManagementStore();

const { dropbox_api_key, is_favourite_enabled, is_se_enabled } = storeToRefs(lycheeStore);
const { canInteractPhoto } = useAlbumActions();
const { is_album_edit_open, is_full_screen, is_touch_select_mode, selectedPhotosIds, selectedAlbumsIds } = storeToRefs(togglableStore);

const { toggleCreateAlbum, toggleImportFromLink, toggleImportFromDropbox, toggleUpload, toggleImportFromServer, toggleCameraCapture } =
	useGalleryModals(togglableStore);

const emits = defineEmits<{
	refresh: [];
	toggleEdit: [];
	goBack: [];
	openSearch: [];
	showSelected: [];
	openContextMenu: [event: MouseEvent];
}>();

const showBreadcrumb = computed(() => albumStore.config?.is_breadcrumb_enabled ?? false);

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

const { addMenu } = useContextMenuAlbumAdd(
	albumStore,
	{
		toggleUpload,
		toggleCameraCapture,
		toggleCreateAlbum,
		toggleImportFromLink,
		toggleUploadTrack,
		deleteTrack,
		toggleImportFromDropbox,
		toggleImportFromServer,
	},
	dropbox_api_key,
);

function toIconifyName(icon: string): string {
	return "prime:" + icon.replace(/^pi\s+pi-/, "").replace(/^pi-/, "");
}

const addMenuSections = computed<DropdownMenuItem[][]>(() => {
	const sections: DropdownMenuItem[][] = [[]];
	for (const entry of addMenu.value as AddMenuItem[]) {
		if ("if" in entry && entry.if === false) {
			continue;
		}
		if ("is_divider" in entry) {
			sections.push([]);
			continue;
		}
		sections[sections.length - 1].push({
			label: trans(entry.label),
			icon: toIconifyName(entry.icon),
			onSelect: entry.callback,
		});
	}
	return sections.filter((s) => s.length > 0);
});

onMounted(() => {
	lycheeStore.load();
});
</script>
