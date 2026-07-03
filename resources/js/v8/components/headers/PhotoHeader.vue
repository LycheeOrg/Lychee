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
		<div class="w-full bg-transparent flex items-center justify-between px-2 h-full">
			<GoBack @go-back="emits('goBack')" />
			<div class="flex items-center">
				<UTooltip
					v-if="!albumStore.rights?.can_edit && leftMenuStore.initData?.root_album?.can_highlight"
					:text="photoStore.photo.is_highlighted ? $t('gallery.photo.actions.unhighlight') : $t('gallery.photo.actions.highlight')"
				>
					<UButton
						variant="ghost"
						:icon="photoStore.photo.is_highlighted ? 'prime:flag-fill' : 'prime:flag'"
						class="ltr:mr-2 rtl:ml-2"
						:class="photoStore.photo.is_highlighted ? 'text-yellow-500' : 'text-white hover:text-yellow-500'"
						color="neutral"
						@click="emits('toggleHighlight')"
					/>
				</UTooltip>
				<div :class="is_slideshow_active ? 'hidden' : 'flex'">
					<UButton
						v-if="is_slideshow_enabled"
						variant="ghost"
						icon="prime:play"
						class="ltr:mr-2 rtl:ml-2"
						color="neutral"
						@click="emits('toggleSlideShow')"
					/>
					<UButton
						v-if="albumStore.rights?.can_access_original && photoStore.photo.size_variants.original?.url"
						variant="ghost"
						icon="prime:window-maximize"
						class="ltr:mr-2 rtl:ml-2 font-bold"
						color="neutral"
						@click="openInNewTab(photoStore.photo.size_variants.original.url)"
					/>
					<UButton
						v-if="albumStore.rights?.can_download"
						variant="ghost"
						icon="prime:cloud-download"
						class="ltr:mr-2 rtl:ml-2"
						color="neutral"
						@click="isDownloadOpen = !isDownloadOpen"
					/>
					<UButton
						v-if="albumStore.rights?.can_edit"
						variant="ghost"
						icon="prime:pencil"
						class="ltr:mr-2 rtl:ml-2"
						color="neutral"
						@click="is_photo_edit_open = !is_photo_edit_open"
					/>
					<UButton
						v-if="!is_exif_disabled"
						variant="ghost"
						icon="prime:info-circle"
						class="ltr:mr-2 rtl:ml-2"
						color="neutral"
						@click="are_details_open = !are_details_open"
					/>
				</div>
			</div>
		</div>
	</header>
	<DownloadPhoto v-model:open="isDownloadOpen" />
</template>
<script setup lang="ts">
import { ref } from "vue";
import DownloadPhoto from "@/v8/components/modals/DownloadPhoto.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import GoBack from "./GoBack.vue";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";

const emits = defineEmits<{
	toggleDetails: [];
	toggleEdit: [];
	toggleSlideShow: [];
	goBack: [];
	toggleHighlight: [];
}>();

const photoStore = usePhotoStore();
const albumStore = useAlbumStore();
const togglableStore = useTogglablesStateStore();
const { is_full_screen, is_photo_edit_open, are_details_open, is_slideshow_active } = storeToRefs(togglableStore);
const isDownloadOpen = ref(false);
const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const { is_exif_disabled, is_slideshow_enabled } = storeToRefs(lycheeStore);

function openInNewTab(url: string) {
	window?.open(url, "_blank")?.focus();
}
</script>
