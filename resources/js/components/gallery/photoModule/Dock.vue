<template>
	<div
		v-if="photoStore.photo"
		:class="{
			'absolute top-0 w-full sm:w-1/2 left-1/2 -translate-x-1/2': true,
			'lg:hover:opacity-100 transition-opacity duration-500 ease-in-out': !isTouchDevice(),
			'opacity-50 lg:opacity-20': !isTouchDevice() && !isFullTransparency,
			'opacity-75': isTouchDevice() && !isFullTransparency,
			'opacity-0': isFullTransparency,
			'z-20 mt-14 sm:mt-0': true,
			'sm:h-1/4': !props.isNarrowMenu,
			'h-14': props.isNarrowMenu,
			hidden: is_slideshow_active,
			'hidden sm:block': are_details_open,
		}"
	>
		<span class="absolute left-1/2 -translate-x-1/2 p-1 min-w-[25%] w-full filter-shadow text-center">
			<DockButton
				v-if="photoStore.photo.is_highlighted"
				v-tooltip.bottom="$t('gallery.photo.actions.unhighlight')"
				pi="flag-fill"
				class="text-yellow-500 lg:hover:text-yellow-100"
				@click="emits('toggleHighlight')"
			/>
			<DockButton
				v-else
				v-tooltip.bottom="$t('gallery.photo.actions.highlight')"
				pi="flag"
				class="text-white lg:hover:text-yellow-500"
				@click="emits('toggleHighlight')"
			/>
			<DockButton
				v-tooltip.bottom="$t('gallery.photo.actions.set_album_header')"
				v-if="!isTimeline"
				pi="image"
				class="lg:hover:text-primary-500 text-white"
				@click="emits('setAlbumHeader')"
			/>
			<DockButton
				v-if="isWatermarkerEnabled"
				v-tooltip.bottom="'Watermark'"
				pi="barcode"
				class="lg:hover:text-primary-500 text-white"
				@click="watermark"
			/>
			<template v-if="lycheeStore.can_rotate">
				<DockButton icon="counterclockwise" class="fill-white lg:hover:fill-primary-500" @click="emits('rotatePhotoCCW')" />
				<DockButton icon="clockwise" class="fill-white lg:hover:fill-primary-500" @click="emits('rotatePhotoCW')" />
			</template>
			<DockButton
				v-tooltip.bottom="$t('gallery.photo.actions.move')"
				pi="folder"
				class="lg:hover:text-primary-500 text-white"
				@click="emits('toggleMove')"
			/>
			<DockButton
				v-tooltip.bottom="$t('gallery.photo.actions.delete')"
				icon="trash"
				class="fill-red-600 lg:fill-white lg:hover:fill-red-600"
				@click="emits('toggleDelete')"
			/>
		</span>
	</div>
</template>
<script setup lang="ts">
import PhotoService from "@/services/photo-service";
import DockButton from "./DockButton.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { computed } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useRoute } from "vue-router";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumStore } from "@/stores/AlbumState";

const toast = useToast();
const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const albumStore = useAlbumStore();
const leftMenu = useLeftMenuStateStore();
const togglableStore = useTogglablesStateStore();
const { is_slideshow_active, are_details_open } = storeToRefs(togglableStore);

const props = defineProps<{
	isNarrowMenu: boolean;
}>();

const isWatermarkerEnabled = computed(
	() =>
		leftMenu.initData?.modules.is_watermarker_enabled &&
		photoStore.photo &&
		albumStore.rights?.can_edit &&
		needSizeVariantsWatermark(photoStore.photo.size_variants),
);

function watermark() {
	if (!photoStore.photo) {
		return;
	}

	PhotoService.watermark([photoStore.photo.id]).then(() => {
		toast.add({
			severity: "success",
			detail: trans("toasts.success"),
			life: 3000,
		});
	});
}

function needSizeVariantsWatermark(sizeVariants: App.Http.Resources.Models.SizeVariantsResouce): boolean {
	return (
		(sizeVariants.thumb && !sizeVariants.thumb.is_watermarked) ||
		(sizeVariants.thumb2x && !sizeVariants.thumb2x.is_watermarked) ||
		(sizeVariants.small && !sizeVariants.small.is_watermarked) ||
		(sizeVariants.small2x && !sizeVariants.small2x.is_watermarked) ||
		(sizeVariants.medium && !sizeVariants.medium.is_watermarked) ||
		(sizeVariants.medium2x && !sizeVariants.medium2x.is_watermarked) ||
		false
	);
}

const emits = defineEmits<{
	toggleHighlight: [];
	setAlbumHeader: [];
	rotatePhotoCCW: [];
	rotatePhotoCW: [];
	toggleMove: [];
	toggleDelete: [];
}>();

const route = useRoute();
const isTimeline = computed(() => route.name === "timeline");

const isFullTransparency = computed(() => {
	if (isTouchDevice()) {
		return lycheeStore.is_mobile_dock_full_transparency_enabled;
	}
	return lycheeStore.is_desktop_dock_full_transparency_enabled;
});
</script>
