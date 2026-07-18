<template>
	<UHeader
		v-if="photoStore.photo"
		id="lychee_toolbar_container"
		:toggle="false"
		:ui="{ root: 'bg-transparent backdrop-blur-none border-b-0' }"
		:class="{
			'absolute top-0 left-0 w-full flex-none z-10 rounded-none': true,
			'opacity-100 md:opacity-0 md:hover:opacity-100': is_full_screen || is_slideshow_active,
			'opacity-100': !is_full_screen && !is_slideshow_active,
		}"
	>
		<template #left>
			<GoBack @go-back="emits('goBack')" />
		</template>

		<template #right>
			<UTooltip
				v-if="!albumStore.rights?.can_edit && leftMenuStore.initData?.root_album?.can_highlight"
				:text="photoStore.photo.is_highlighted ? $t('gallery.photo.actions.unhighlight') : $t('gallery.photo.actions.highlight')"
			>
				<UButton
					variant="ghost"
					icon="lucide:flag"
					:ui="{ leadingIcon: photoStore.photo.is_highlighted ? FILL_OVERRIDE_CLASS : '' }"
					:class="photoStore.photo.is_highlighted ? 'text-yellow-500' : 'text-white hover:text-yellow-500'"
					color="neutral"
					@click="emits('toggleHighlight')"
				/>
			</UTooltip>
			<div class="flex items-center gap-1.5" :class="is_slideshow_active ? 'hidden' : 'flex'">
				<UButton v-if="is_slideshow_enabled" variant="ghost" icon="lucide:play" color="neutral" @click="emits('toggleSlideShow')" />
				<UButton
					v-if="albumStore.rights?.can_access_original && photoStore.photo.size_variants.original?.url"
					variant="ghost"
					icon="lucide:app-window"
					class="font-bold"
					color="neutral"
					@click="openInNewTab(photoStore.photo.size_variants.original.url)"
				/>
				<UButton
					v-if="albumStore.rights?.can_download"
					variant="ghost"
					icon="lucide:cloud-download"
					color="neutral"
					@click="
						() => {
							isDownloadOpen = !isDownloadOpen;
						}
					"
				/>
				<UButton
					v-if="albumStore.rights?.can_edit"
					variant="ghost"
					icon="lucide:pencil"
					color="neutral"
					@click="
						() => {
							is_photo_edit_open = !is_photo_edit_open;
						}
					"
				/>
				<UButton
					v-if="!is_exif_disabled"
					variant="ghost"
					icon="lucide:info"
					color="neutral"
					@click="
						() => {
							are_details_open = !are_details_open;
						}
					"
				/>
				<UTooltip v-if="is_photo_share_card_enabled" :text="$t('gallery.photo.actions.share_card')">
					<UButton
						variant="ghost"
						icon="lucide:qr-code"
						color="neutral"
						@click="
							() => {
								isPhotoShareCardOpen = !isPhotoShareCardOpen;
							}
						"
					/>
				</UTooltip>
			</div>
		</template>
	</UHeader>
	<DownloadPhoto v-model:open="isDownloadOpen" />
	<PhotoShareCard v-model:open="isPhotoShareCardOpen" />
</template>
<script setup lang="ts">
import { ref } from "vue";
import DownloadPhoto from "@/v8/components/modals/DownloadPhoto.vue";
import PhotoShareCard from "@/v8/components/modals/PhotoShareCard.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import GoBack from "./GoBack.vue";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";

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
const isPhotoShareCardOpen = ref(false);
const lycheeStore = useLycheeStateStore();
const leftMenuStore = useLeftMenuStateStore();
const { is_exif_disabled, is_slideshow_enabled, is_photo_share_card_enabled } = storeToRefs(lycheeStore);

function openInNewTab(url: string) {
	window?.open(url, "_blank")?.focus();
}
</script>
