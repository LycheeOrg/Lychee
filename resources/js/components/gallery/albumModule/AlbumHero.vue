<template>
	<div v-if="props.album.preFormattedData.url" class="w-full h-1/2 relative">
		<img class="absolute block top-0 left-0 w-full h-full object-cover object-center z-0" :src="props.album.preFormattedData.url" />
		<div class="h-full ltr:pl-7 rtl:pr-7 pt-7 relative text-shadow-sm w-full bg-gradient-to-b from-black/20 via-80%">
			<h1 class="font-bold text-4xl text-surface-0">{{ props.album.title }}</h1>
			<span v-if="props.album.preFormattedData.min_max_text" class="text-surface-200 text-sm">
				{{ props.album.preFormattedData.min_max_text }}
			</span>
		</div>
	</div>
	<Card class="w-full">
		<template #content>
			<div class="w-full flex flex-row-reverse items-start">
				<div class="order-1 flex flex-col w-full">
					<h1 v-if="!props.album.preFormattedData.url" class="font-bold text-2xl">{{ props.album.title }}</h1>
					<span v-if="props.album.preFormattedData.created_at" class="block text-muted-color text-sm">
						{{ $t("gallery.album.hero.created") }} {{ props.album.preFormattedData.created_at }}
					</span>
					<span v-if="props.album.preFormattedData.copyright" class="block text-muted-color text-sm">
						{{ $t("gallery.album.hero.copyright") }} {{ props.album.preFormattedData.copyright }}
					</span>
					<span v-if="props.album.preFormattedData.num_children" class="block text-muted-color text-sm">
						{{ props.album.preFormattedData.num_children }} {{ $t("gallery.album.hero.subalbums") }}
					</span>
					<span v-if="props.album.preFormattedData.num_photos" class="block text-muted-color text-sm">
						{{ props.album.preFormattedData.num_photos }} {{ $t("gallery.album.hero.images") }}
						<span v-if="props.album.preFormattedData.license" class="text-muted-color text-sm">
							&mdash; {{ props.album.preFormattedData.license }}
						</span>
					</span>
				</div>
				<div class="flex flex-col w-full gap-2">
					<div class="flex flex-row-reverse items-center">
						<a
							v-if="props.album.rights.can_download"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							:title="$t('gallery.album.hero.download')"
							@click="download"
						>
							<i class="pi pi-cloud-download" />
						</a>
						<a
							v-if="props.album.rights.can_share"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							:title="$t('gallery.album.hero.share')"
							@click="openSharingModal"
						>
							<i class="pi pi-share-alt" />
						</a>
						<a
							v-if="is_se_enabled && user?.id !== null"
							class="shrink-0 px-3 cursor-pointer inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="openStatistics"
						>
							<i class="pi pi-chart-scatter text-primary-emphasis" />
						</a>
						<a
							v-if="is_se_preview_enabled && user?.id !== null"
							v-tooltip.left="$t('gallery.album.hero.stats_only_se')"
							class="shrink-0 px-3 cursor-not-allowed text-primary-emphasis"
						>
							<i class="pi pi-chart-scatter" />
						</a>
						<router-link
							v-if="props.config.is_mod_frame_enabled"
							v-tooltip.bottom="'Frame'"
							:to="{ name: 'frame', params: { albumId: props.album.id } }"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
						>
							<i class="pi pi-desktop" />
						</router-link>
						<router-link
							v-if="props.config.is_map_accessible && hasCoordinates"
							:to="{ name: 'map', params: { albumId: props.album.id } }"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
						>
							<i class="pi pi-map" />
						</router-link>
						<a
							v-if="props.album.photos.length > 0 && is_slideshow_enabled"
							v-tooltip.bottom="'Start slideshow'"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="emits('toggleSlideShow')"
						>
							<i class="pi pi-play" />
						</a>
						<a
							v-if="isWatermarkerEnabled"
							v-tooltip.bottom="'Watermark'"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="watermark"
						>
							<i class="pi pi-barcode" />
						</a>

						<template v-if="isTouchDevice() && user?.id !== null">
							<a
								v-if="props.hasHidden && are_nsfw_visible"
								class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
								:title="'hide hidden'"
								@click="are_nsfw_visible = false"
							>
								<i class="pi pi pi-eye-slash" />
							</a>
							<a
								v-if="props.hasHidden && !are_nsfw_visible"
								class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
								:title="'show hidden'"
								@click="are_nsfw_visible = true"
							>
								<i class="pi pi-eye" />
							</a>
						</template>
					</div>
					<AlbumStatistics v-if="props.album.statistics" :stats="props.album.statistics" />
				</div>
			</div>
			<div
				v-if="props.album.preFormattedData.description"
				class="w-full max-w-full my-4 text-justify text-muted-color text-base/5 prose dark:prose-invert prose-sm"
				v-html="props.album.preFormattedData.description"
			/>
		</template>
	</Card>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import Card from "primevue/card";
import { computed } from "vue";
import AlbumStatistics from "./AlbumStatistics.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const toast = useToast();
const auth = useAuthStore();
const leftMenu = useLeftMenuStateStore();
const lycheeStore = useLycheeStateStore();
const { is_se_enabled, is_se_preview_enabled, are_nsfw_visible, is_slideshow_enabled } = storeToRefs(lycheeStore);
const { user } = storeToRefs(auth);

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	hasHidden: boolean;
	config: {
		is_map_accessible: boolean;
		is_mod_frame_enabled: boolean;
	};
}>();

const hasCoordinates = computed(() => props.album.photos.some((photo) => photo.latitude !== null && photo.longitude !== null));

const isWatermarkerEnabled = computed(
	() =>
		leftMenu.initData?.modules.is_watermarker_enabled &&
		props.album.rights.can_edit &&
		props.album.photos.some((p) => needSizeVariantsWatermark(p.size_variants)),
);
function watermark() {
	AlbumService.watermark(props.album.id).then(() => {
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
	openSharingModal: [];
	openStatistics: [];
	toggleSlideShow: [];
}>();

function openSharingModal() {
	emits("openSharingModal");
}

function openStatistics() {
	emits("openStatistics");
}

function download() {
	AlbumService.download([props.album?.id]);
}
</script>
