<template>
	<div v-if="albumStore.album && albumStore.album.preFormattedData.url" class="w-full h-1/2 relative">
		<img class="absolute block top-0 left-0 w-full h-full object-cover object-center z-0" :src="albumStore.album.preFormattedData.url" />
		<div class="h-full ltr:pl-7 rtl:pr-7 pt-7 relative text-shadow-sm w-full bg-linear-to-b from-black/20 via-80%">
			<h1 class="font-bold text-4xl text-surface-0">{{ albumStore.album.title }}</h1>
			<span v-if="albumStore.album.preFormattedData.min_max_text" class="text-surface-200 text-sm">
				{{ albumStore.album.preFormattedData.min_max_text }}
			</span>
		</div>
	</div>
	<Card class="w-full" v-if="albumStore.album">
		<template #content>
			<div class="w-full flex flex-row-reverse items-start">
				<div class="order-1 flex flex-col w-full">
					<h1 v-if="!albumStore.album.preFormattedData.url" class="font-bold text-2xl">{{ albumStore.album.title }}</h1>
					<span v-if="albumStore.album.preFormattedData.created_at" class="block text-muted-color text-sm">
						{{ $t("gallery.album.hero.created") }} {{ albumStore.album.preFormattedData.created_at }}
					</span>
					<span v-if="albumStore.album.preFormattedData.copyright" class="block text-muted-color text-sm">
						{{ $t("gallery.album.hero.copyright") }} {{ albumStore.album.preFormattedData.copyright }}
					</span>
					<span v-if="albumStore.album.preFormattedData.num_children" class="block text-muted-color text-sm">
						{{ albumStore.album.preFormattedData.num_children }} {{ $t("gallery.album.hero.subalbums") }}
					</span>
					<span v-if="albumStore.album.preFormattedData.num_photos" class="block text-muted-color text-sm">
						{{ albumStore.album.preFormattedData.num_photos }} {{ $t("gallery.album.hero.images") }}
						<span v-if="albumStore.album.preFormattedData.license" class="text-muted-color text-sm">
							&mdash; {{ albumStore.album.preFormattedData.license }}
						</span>
					</span>
				</div>
				<div class="flex flex-col w-full gap-2">
					<div class="flex flex-row-reverse items-center">
						<a
							v-if="albumStore.rights?.can_download"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							:title="$t('gallery.album.hero.download')"
							@click="download"
						>
							<i class="pi pi-cloud-download" />
						</a>
						<a
							v-if="albumStore.rights?.can_share"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							:title="$t('gallery.album.hero.share')"
							@click="openSharingModal"
						>
							<i class="pi pi-share-alt" />
						</a>
						<a
							v-if="isEmbeddable"
							v-tooltip.bottom="$t('gallery.album.hero.embed')"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="openEmbedCode"
						>
							<i class="pi pi-code" />
						</a>
						<a
							v-if="is_se_enabled && userStore.isLoggedIn"
							class="shrink-0 px-3 cursor-pointer inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="openStatistics"
						>
							<i class="pi pi-chart-scatter text-primary-emphasis" />
						</a>
						<a
							v-if="is_se_preview_enabled && userStore.isLoggedIn"
							v-tooltip.left="$t('gallery.album.hero.stats_only_se')"
							class="shrink-0 px-3 cursor-not-allowed text-primary-emphasis"
						>
							<i class="pi pi-chart-scatter" />
						</a>
						<router-link
							v-if="albumStore.config?.is_mod_frame_enabled"
							v-tooltip.bottom="'Frame'"
							:to="{ name: 'frame', params: { albumId: albumStore.album.id } }"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
						>
							<i class="pi pi-desktop" />
						</router-link>
						<router-link
							v-if="albumStore.config?.is_map_accessible && hasCoordinates"
							:to="{ name: 'map', params: { albumId: albumStore.album.id } }"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
						>
							<i class="pi pi-map" />
						</router-link>
						<a
							v-if="photosStore.photos.length > 0 && is_slideshow_enabled"
							v-tooltip.bottom="'Start slideshow'"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="emits('toggleSlideShow')"
						>
							<i class="pi pi-play" />
						</a>
						<a
							v-if="isRenamerEnabled"
							v-tooltip.bottom="$t('gallery.album.hero.apply_renamer')"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="emits('toggleApplyRenamer')"
						>
							<i class="pi pi-pencil" />
						</a>
						<a
							v-if="isWatermarkerEnabled"
							v-tooltip.bottom="$t('gallery.album.hero.watermark')"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							@click="emits('toggleWatermarkConfirm')"
						>
							<i class="pi pi-barcode" />
						</a>

						<!-- Album view toggle buttons -->
						<Button
							v-if="lycheeStore.album_view_mode === 'list' && albumsStore.albums.length > 0"
							icon="pi pi-th-large"
							class="border-none"
							severity="secondary"
							text
							@click="toggleAlbumView('grid')"
						/>
						<Button
							v-else-if="albumsStore.albums.length > 0"
							icon="pi pi-list"
							class="border-none"
							severity="secondary"
							text
							@click="toggleAlbumView('list')"
						/>

						<template v-if="isTouchDevice() && userStore.isLoggedIn">
							<a
								v-if="albumsStore.hasHidden && are_nsfw_visible"
								class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
								:title="'hide hidden'"
								@click="are_nsfw_visible = false"
							>
								<i class="pi pi pi-eye-slash" />
							</a>
							<a
								v-if="albumsStore.hasHidden && !are_nsfw_visible"
								class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
								:title="'show hidden'"
								@click="are_nsfw_visible = true"
							>
								<i class="pi pi-eye" />
							</a>
						</template>
					</div>
					<AlbumStatistics v-if="albumStore.album.statistics" :stats="albumStore.album.statistics" />
				</div>
			</div>
			<div
				v-if="albumStore.album.preFormattedData.description"
				class="w-full max-w-full my-4 text-justify text-muted-color text-base/5 prose dark:prose-invert prose-sm"
				v-html="albumStore.album.preFormattedData.description"
			/>
		</template>
	</Card>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import Card from "primevue/card";
import Button from "primevue/button";
import { computed } from "vue";
import AlbumStatistics from "./AlbumStatistics.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";

const userStore = useUserStore();
const leftMenu = useLeftMenuStateStore();
const lycheeStore = useLycheeStateStore();
const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const photosStore = usePhotosStore();

const { is_se_enabled, is_se_preview_enabled, are_nsfw_visible, is_slideshow_enabled } = storeToRefs(lycheeStore);

function toggleAlbumView(mode: "grid" | "list") {
	lycheeStore.album_view_mode = mode;
}

const hasCoordinates = computed(() =>
	photosStore.photos.some((photo) => photo.precomputed.latitude !== null && photo.precomputed.longitude !== null),
);

const isRenamerEnabled = computed(() => leftMenu.initData?.modules.is_mod_renamer_enabled && albumStore.rights?.can_edit);

const isWatermarkerEnabled = computed(
	() =>
		leftMenu.initData?.modules.is_watermarker_enabled &&
		albumStore.rights?.can_edit &&
		photosStore.photos.some((p) => needSizeVariantsWatermark(p.size_variants)),
);

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
	openEmbedCode: [];
	toggleApplyRenamer: [];
	toggleWatermarkConfirm: [];
}>();

// Check if album is embeddable (public, no password, no link requirement)
// and if user is logged in
const isEmbeddable = computed(() => {
	// Only show embed button to logged-in users
	if (!userStore.isLoggedIn) {
		return false;
	}
	if (!albumStore.album) {
		return false;
	}
	// Album must be public and not password/link protected for embedding
	const policy = albumStore.album.policy;
	return policy.is_public && !policy.is_password_required && !policy.is_link_required;
});

function openSharingModal() {
	emits("openSharingModal");
}

function openStatistics() {
	emits("openStatistics");
}

function openEmbedCode() {
	emits("openEmbedCode");
}

function download() {
	if (albumStore.album === undefined) {
		return;
	}
	AlbumService.download([albumStore.album.id]);
}
</script>
