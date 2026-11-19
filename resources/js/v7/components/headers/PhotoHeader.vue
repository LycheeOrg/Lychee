<template>
	<header
		v-if="photoStore.photo"
		id="lychee_toolbar_container"
		:class="{
			'absolute top-0 left-0 w-full flex-none z-10 h-14 rounded-none': true,
			'opacity-100 md:opacity-0 md:hover:opacity-100': is_full_screen || is_slideshow_active,
			'opacity-100 h-14': !is_full_screen && !is_slideshow_active,
		}"
	>
		<Toolbar class="w-full bg-transparent border-0">
			<template #start>
				<GoBack @go-back="emits('goBack')" />
			</template>
			<template #end>
				<Button
					v-if="!albumStore.rights?.can_edit && rights?.root_album?.can_highlight"
					text
					v-tooltip.bottom="
						photoStore.photo.is_highlighted ? $t('gallery.photo.actions.unhighlight') : $t('gallery.photo.actions.highlight')
					"
					:icon="photoStore.photo.is_highlighted ? 'pi pi-flag-fill' : 'pi pi-flag'"
					class="ltr:mr-2 rtl:ml-2"
					:class="
						photoStore.photo.is_highlighted
							? '[&>span]:text-yellow-500 lg:hover:[&>span]:text-yellow-100'
							: '[&>span]:text-white lg:hover:[&>span]:text-yellow-500'
					"
					severity="secondary"
					@click="emits('toggleHighlight')"
				/>
				<div :class="is_slideshow_active ? 'hidden' : 'flex'">
					<Button
						v-if="is_slideshow_enabled"
						text
						icon="pi pi-play"
						class="ltr:mr-2 rtl:ml-2"
						severity="secondary"
						@click="emits('toggleSlideShow')"
					/>
					<Button
						v-if="albumStore.rights?.can_access_original && photoStore.photo.size_variants.original?.url"
						text
						icon="pi pi-window-maximize"
						class="ltr:mr-2 rtl:ml-2 font-bold"
						severity="secondary"
						@click="openInNewTab(photoStore.photo.size_variants.original.url)"
					/>
					<Button
						v-if="albumStore.rights?.can_download"
						text
						icon="pi pi-cloud-download"
						class="ltr:mr-2 rtl:ml-2"
						severity="secondary"
						@click="isDownloadOpen = !isDownloadOpen"
					/>
					<Button
						v-if="canEditCurrentPhoto"
						text
						icon="pi pi-pencil"
						class="ltr:mr-2 rtl:ml-2"
						severity="secondary"
						@click="is_photo_edit_open = !is_photo_edit_open"
					/>
					<Button
						v-if="!is_exif_disabled"
						icon="pi pi-info"
						class="ltr:mr-2 rtl:ml-2"
						severity="secondary"
						text
						@click="are_details_open = !are_details_open"
					/>
				</div>
			</template>
		</Toolbar>
	</header>
	<DownloadPhoto v-model:visible="isDownloadOpen" />
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { computed, ref } from "vue";
import DownloadPhoto from "@/v7/components/modals/DownloadPhoto.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import GoBack from "./GoBack.vue";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useUserStore } from "@/stores/UserState";
import { useGlobalRights } from "@/composables/useGlobalRights";

const emits = defineEmits<{
	toggleDetails: [];
	toggleEdit: [];
	toggleSlideShow: [];
	goBack: [];
	toggleHighlight: [];
}>();

const photoStore = usePhotoStore();
const albumStore = useAlbumStore();
const userStore = useUserStore();
const togglableStore = useTogglablesStateStore();
const { is_full_screen, is_photo_edit_open, are_details_open, is_slideshow_active } = storeToRefs(togglableStore);
const isDownloadOpen = ref(false);
const lycheeStore = useLycheeStateStore();
const { rights } = useGlobalRights();
const { is_exif_disabled, is_slideshow_enabled } = storeToRefs(lycheeStore);

function openInNewTab(url: string) {
	window?.open(url, "_blank")?.focus();
}

/**
 * `albumStore.rights.can_edit` is album-wide, not per-photo - fine for a
 * regular Album/TagAlbum/PersonAlbum, but not for a smart album, whose
 * photos are no longer guaranteed all-owned-by-viewer once
 * `SA_override_visibility` is enabled while `AlbumPolicy::canEdit()` always
 * grants smart albums edit rights regardless of which photos it surfaces.
 * `photoStore.photo.owner_id` is only populated for smart albums (see
 * `PhotoResource.php`).
 */
const canEditCurrentPhoto = computed(() => {
	if (!albumStore.rights?.can_edit) {
		return false;
	}
	if (albumStore.smartAlbum !== undefined) {
		return photoStore.photo?.owner_id === userStore.user?.id;
	}
	return true;
});
</script>
