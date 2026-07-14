<template>
	<AlbumHeaderPanel
		v-if="albumStore.album && albumStore.album.preFormattedData.url"
		:album="albumStore.album"
		@scroll-to-pictures="emits('scrollToPictures')"
	/>
	<UCard
		class="w-full"
		v-if="albumStore.album"
		:class="{ '-mt-22 z-10 relative': album_header_size !== 'half_screen' && albumStore.album && albumStore.album.preFormattedData.url }"
	>
		<div class="w-full flex flex-col gap-2 md:flex-row-reverse items-start">
			<div class="order-1 flex flex-col w-full">
				<h1 v-if="!albumStore.album.preFormattedData.url" class="font-bold text-2xl">{{ albumStore.album.title }}</h1>
				<span v-if="albumStore.album.preFormattedData.created_at" class="block text-muted text-sm">
					{{ $t("gallery.album.hero.created") }} {{ albumStore.album.preFormattedData.created_at }}
				</span>
				<span v-if="albumStore.album.preFormattedData.copyright" class="block text-muted text-sm">
					{{ $t("gallery.album.hero.copyright") }} {{ albumStore.album.preFormattedData.copyright }}
				</span>
				<span v-if="albumStore.album.preFormattedData.num_children" class="block text-muted text-sm">
					{{ albumStore.album.preFormattedData.num_children }} {{ $t("gallery.album.hero.subalbums") }}
				</span>
				<span v-if="albumStore.album.preFormattedData.num_photos" class="block text-muted text-sm">
					{{ albumStore.album.preFormattedData.num_photos }} {{ $t("gallery.album.hero.images") }}
					<span v-if="albumStore.album.preFormattedData.license" class="text-muted text-sm">
						&mdash; {{ albumStore.album.preFormattedData.license }}
					</span>
				</span>
				<span v-if="albumTags.length > 0" class="flex flex-wrap gap-1.5 mt-1">
					<span v-for="tag in albumTags" :key="`album-tag-${tag}`" class="text-xs rounded-full py-1 px-2.5 bg-black/50 cursor-default">
						{{ tag }}
					</span>
				</span>
				<span
					v-if="isFaceRecognitionEnabled && albumStore.album_people_total > 0"
					class="block text-muted text-sm cursor-pointer hover:text-default transition-colors duration-150"
					@click="isPeopleOpen = !isPeopleOpen"
				>
					{{ trans_choice("people.people_detected", albumStore.album_people_total, { count: albumStore.album_people_total.toString() }) }}
					<UIcon :name="isPeopleOpen ? 'prime:chevron-up' : 'prime:chevron-down'" class="text-xs ml-1" />
				</span>
			</div>
			<div class="flex flex-col w-full gap-2">
				<div class="flex flex-row-reverse items-center">
					<a
						v-if="albumStore.rights?.can_download"
						class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
						:title="$t('gallery.album.hero.download')"
						@click="download"
					>
						<UIcon name="prime:cloud-download" />
					</a>
					<a
						v-if="albumStore.rights?.can_share"
						class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
						:title="$t('gallery.album.hero.share')"
						@click="openSharingModal"
					>
						<UIcon name="prime:share-alt" />
					</a>
					<UTooltip v-if="isEmbeddable" :text="$t('gallery.album.hero.embed')">
						<a
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							@click="openEmbedCode"
						>
							<UIcon name="prime:code" />
						</a>
					</UTooltip>
					<a
						v-if="is_se_enabled && userStore.isLoggedIn"
						class="shrink-0 px-3 cursor-pointer inline-block transform duration-300 hover:scale-150 hover:text-default"
						@click="openStatistics"
					>
						<UIcon name="prime:chart-scatter" class="text-primary" />
					</a>
					<UTooltip v-if="is_se_preview_enabled && userStore.isLoggedIn" :text="$t('gallery.album.hero.stats_only_se')">
						<a class="shrink-0 px-3 cursor-not-allowed text-primary">
							<UIcon name="prime:chart-scatter" />
						</a>
					</UTooltip>
					<UTooltip v-if="albumStore.config?.is_mod_frame_enabled" text="Frame">
						<RouterLink
							:to="{ name: 'frame', params: { albumId: albumStore.album.id } }"
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
						>
							<UIcon name="prime:desktop" />
						</RouterLink>
					</UTooltip>
					<RouterLink
						v-if="albumStore.config?.is_map_accessible && hasCoordinates"
						:to="{ name: 'map', params: { albumId: albumStore.album.id } }"
						class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
					>
						<UIcon name="prime:map" />
					</RouterLink>
					<UTooltip v-if="photosStore.photos.length > 0 && is_slideshow_enabled" text="Start slideshow">
						<a
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							@click="emits('toggleSlideShow')"
						>
							<UIcon name="prime:play" />
						</a>
					</UTooltip>
					<UTooltip v-if="isRenamerEnabled" :text="$t('gallery.album.hero.apply_renamer')">
						<a
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							@click="emits('toggleApplyRenamer')"
						>
							<UIcon name="prime:pencil" />
						</a>
					</UTooltip>
					<UTooltip v-if="isWatermarkerEnabled" :text="$t('gallery.album.hero.watermark')">
						<a
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							@click="emits('toggleWatermarkConfirm')"
						>
							<UIcon name="prime:barcode" />
						</a>
					</UTooltip>
					<UTooltip v-if="isFaceScanEnabled" :text="$t('people.scan_faces')">
						<a
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							@click="emits('toggleScanFaces')"
						>
							<UIcon name="prime:face-smile" />
						</a>
					</UTooltip>

					<!-- Album view toggle buttons -->
					<UButton
						v-if="lycheeStore.album_view_mode === 'list' && albumsStore.albums.length > 0"
						icon="prime:th-large"
						color="neutral"
						variant="ghost"
						@click="toggleAlbumView('grid')"
					/>
					<UButton
						v-else-if="albumsStore.albums.length > 0"
						icon="prime:list"
						color="neutral"
						variant="ghost"
						@click="toggleAlbumView('list')"
					/>

					<template v-if="isTouchDevice() && userStore.isLoggedIn">
						<a
							v-if="albumsStore.hasHidden && are_nsfw_visible"
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							:title="'hide hidden'"
							@click="are_nsfw_visible = false"
						>
							<UIcon name="prime:eye-slash" />
						</a>
						<a
							v-if="albumsStore.hasHidden && !are_nsfw_visible"
							class="shrink-0 px-3 cursor-pointer text-muted inline-block transform duration-300 hover:scale-150 hover:text-default"
							:title="'show hidden'"
							@click="are_nsfw_visible = true"
						>
							<UIcon name="prime:eye" />
						</a>
					</template>
				</div>
				<AlbumStatistics v-if="albumStore.album.statistics" :stats="albumStore.album.statistics" />
			</div>
		</div>
		<div
			v-if="albumStore.album.preFormattedData.description"
			class="w-full max-w-full my-4 text-justify text-muted text-base/5 prose dark:prose-invert prose-sm"
			v-html="albumStore.album.preFormattedData.description"
		/>
		<AlbumPeopleFilter v-if="isFaceRecognitionEnabled && isPeopleOpen && albumStore.album_people.length > 0" class="mt-2" />
	</UCard>
</template>
<script setup lang="ts">
import { trans_choice } from "laravel-vue-i18n";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import AlbumStatistics from "./AlbumStatistics.vue";
import AlbumPeopleFilter from "./AlbumPeopleFilter.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import AlbumHeaderPanel from "./AlbumHeaderPanel.vue";

const userStore = useUserStore();
const leftMenu = useLeftMenuStateStore();
const lycheeStore = useLycheeStateStore();
const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const photosStore = usePhotosStore();

const { is_se_enabled, is_se_preview_enabled, are_nsfw_visible, is_slideshow_enabled, album_header_size, is_embed_enabled } =
	storeToRefs(lycheeStore);

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

const isFaceRecognitionEnabled = computed(() => leftMenu.initData?.modules.is_face_recognition_enabled === true);

// `editable` is only populated by the backend when the current user has edit
// rights on the album (owner or admin), so its presence is enough to gate
// display of the album's own tags here.
const albumTags = computed(() => albumStore.tagOrModelAlbum?.editable?.tags ?? []);

const isPeopleOpen = ref(false);

// Load people whenever the album changes (and AI vision is on)
watch(
	() => albumStore.albumId,
	(id) => {
		if (id && isFaceRecognitionEnabled.value) {
			isPeopleOpen.value = false;
			albumStore.loadAlbumPeople();
		}
	},
	{ immediate: true },
);

const isFaceScanEnabled = computed(() => isFaceRecognitionEnabled.value && albumStore.rights?.can_edit && photosStore.photos.length > 0);

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
	scrollToPictures: [];
	toggleApplyRenamer: [];
	toggleWatermarkConfirm: [];
	toggleDownloadAlbum: [];
	toggleScanFaces: [];
}>();

// Check if album is embeddable (public, no password, no link requirement)
// and if user is logged in
const isEmbeddable = computed(() => {
	if (!is_embed_enabled.value) {
		return false;
	}
	if (!userStore.isLoggedIn) {
		return false;
	}
	if (!albumStore.album) {
		return false;
	}
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
	emits("toggleDownloadAlbum");
}
</script>
