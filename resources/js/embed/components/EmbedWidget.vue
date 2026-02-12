<template>
	<div :class="['lychee-embed', config.containerClass]" :style="containerStyle">
		<div v-if="loading" class="lychee-embed__loading">Loading...</div>

		<div v-else-if="error" class="lychee-embed__error">
			{{ error }}
		</div>

		<div v-else-if="albumData" class="lychee-embed__content">
			<!-- Album header (top placement) -->
			<div v-if="config.headerPlacement === 'top' && (config.showTitle || config.showDescription)" class="lychee-embed__header">
				<div class="lychee-embed__header-content">
					<h2 v-if="config.showTitle" class="lychee-embed__title">
						{{ albumData.album.title }}
						<a :href="galleryUrl" target="_blank" rel="noopener noreferrer" class="lychee-embed__gallery-link" title="View in Gallery">
							↗
						</a>
					</h2>
					<p v-if="config.showDescription && albumData.album.description" class="lychee-embed__description">
						{{ albumData.album.description }}
					</p>
				</div>
			</div>

			<!-- Filmstrip layout -->
			<div
				v-if="isFilmstripMode && filmstripLayout"
				ref="gridContainer"
				class="lychee-embed__filmstrip"
				:style="{ height: `${containerHeight}px` }"
			>
				<!-- Main viewer -->
				<div
					class="lychee-embed__filmstrip-main"
					:style="{
						position: 'absolute',
						top: `${filmstripLayout.mainViewer.top}px`,
						left: `${filmstripLayout.mainViewer.left}px`,
						width: `${filmstripLayout.mainViewer.width}px`,
						height: `${filmstripLayout.mainViewer.height}px`,
					}"
				>
					<img
						v-if="albumData && albumData.photos[filmstripActiveIndex]"
						:src="
							getBestSizeVariant({
								...albumData.photos[filmstripActiveIndex],
								position: { top: 0, left: 0, width: filmstripLayout.mainViewer.width, height: filmstripLayout.mainViewer.height },
							})
						"
						:alt="albumData.photos[filmstripActiveIndex].title || 'Photo'"
						:title="albumData.photos[filmstripActiveIndex].title || undefined"
						class="lychee-embed__filmstrip-main-img"
						role="button"
						tabindex="0"
						@click="openLightbox(albumData.photos[filmstripActiveIndex].id)"
						@keydown.enter="openLightbox(albumData.photos[filmstripActiveIndex].id)"
						@keydown.space.prevent="openLightbox(albumData.photos[filmstripActiveIndex].id)"
					/>
					<!-- Video play icon overlay for filmstrip main viewer -->
					<div v-if="albumData && albumData.photos[filmstripActiveIndex]?.is_video" class="lychee-embed__video-overlay">
						<svg class="lychee-embed__play-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
							<circle cx="50" cy="50" r="45" fill="rgba(0, 0, 0, 0.6)" />
							<polygon points="37,30 37,70 70,50" fill="white" />
						</svg>
					</div>

					<!-- Navigation arrows -->
					<button
						v-if="filmstripActiveIndex > 0"
						class="lychee-embed__filmstrip-nav lychee-embed__filmstrip-nav--prev"
						@click="selectFilmstripPhoto(filmstripActiveIndex - 1)"
						aria-label="Previous photo"
						title="Previous"
					>
						<span class="lychee-embed__filmstrip-nav-icon">‹</span>
					</button>

					<button
						v-if="albumData && filmstripActiveIndex < albumData.photos.length - 1"
						class="lychee-embed__filmstrip-nav lychee-embed__filmstrip-nav--next"
						@click="selectFilmstripPhoto(filmstripActiveIndex + 1)"
						aria-label="Next photo"
						title="Next"
					>
						<span class="lychee-embed__filmstrip-nav-icon">›</span>
					</button>
				</div>

				<!-- Thumbnail strip -->
				<div
					class="lychee-embed__filmstrip-thumbnails"
					:style="{
						position: 'absolute',
						top: `${filmstripLayout.thumbnailStrip.top}px`,
						left: `${filmstripLayout.thumbnailStrip.left}px`,
						width: `${filmstripLayout.thumbnailStrip.width}px`,
						height: `${filmstripLayout.thumbnailStrip.height}px`,
					}"
				>
					<div
						v-for="(thumb, index) in filmstripLayout.thumbnails"
						:key="thumb.photo.id"
						class="lychee-embed__filmstrip-thumb"
						:class="{ 'lychee-embed__filmstrip-thumb--active': index === filmstripActiveIndex }"
						role="button"
						tabindex="0"
						:aria-label="`Select ${thumb.photo.title || 'photo'}`"
						:style="{
							position: 'absolute',
							top: `${thumb.position.top}px`,
							left: `${thumb.position.left}px`,
							width: `${thumb.position.width}px`,
							height: `${thumb.position.height}px`,
						}"
						@click="selectFilmstripPhoto(index)"
						@keydown.enter="selectFilmstripPhoto(index)"
						@keydown.space.prevent="selectFilmstripPhoto(index)"
					>
						<img
							:src="getBestSizeVariant({ ...thumb.photo, position: thumb.position })"
							:alt="thumb.photo.title || 'Photo'"
							:title="thumb.photo.title || undefined"
							loading="lazy"
							class="lychee-embed__photo-img"
						/>
						<!-- Video play icon overlay -->
						<div v-if="thumb.photo.is_video" class="lychee-embed__video-overlay">
							<svg class="lychee-embed__play-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
								<circle cx="50" cy="50" r="45" fill="rgba(0, 0, 0, 0.6)" />
								<polygon points="37,30 37,70 70,50" fill="white" />
							</svg>
						</div>
					</div>
				</div>
			</div>

			<!-- Regular photo grid with selected layout -->
			<div v-else ref="gridContainer" class="lychee-embed__grid" :style="{ height: `${containerHeight}px` }">
				<div
					v-for="photo in positionedPhotos"
					:key="photo.id"
					class="lychee-embed__photo"
					role="button"
					tabindex="0"
					:aria-label="`View ${photo.title || 'photo'} in lightbox`"
					:style="{
						position: 'absolute',
						top: `${photo.position.top}px`,
						left: `${photo.position.left}px`,
						width: `${photo.position.width}px`,
						height: `${photo.position.height}px`,
					}"
					@click="openLightbox(photo.id)"
					@keydown.enter="openLightbox(photo.id)"
					@keydown.space.prevent="openLightbox(photo.id)"
				>
					<img
						:src="getBestSizeVariant(photo)"
						:alt="photo.title || 'Photo'"
						:title="photo.title || undefined"
						loading="lazy"
						class="lychee-embed__photo-img"
					/>
					<!-- Video play icon overlay -->
					<div v-if="photo.is_video" class="lychee-embed__video-overlay">
						<svg class="lychee-embed__play-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
							<circle cx="50" cy="50" r="45" fill="rgba(0, 0, 0, 0.6)" />
							<polygon points="37,30 37,70 70,50" fill="white" />
						</svg>
					</div>
				</div>
			</div>

			<!-- Album link (bottom placement) -->
			<div v-if="config.headerPlacement === 'bottom'" class="lychee-embed__footer">
				<a :href="galleryUrl" target="_blank" rel="noopener noreferrer" class="lychee-embed__footer-link">
					View "{{ albumData.album.title }}" in Gallery ↗
				</a>
			</div>

			<!-- Lightbox -->
			<Lightbox
				v-if="albumData"
				:photos="albumData.photos"
				:initial-index="lightboxPhotoIndex"
				:is-open="lightboxOpen"
				:config="config"
				@close="closeLightbox"
				@update:current-index="lightboxPhotoIndex = $event"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import type { EmbedConfig, EmbedApiResponse, PositionedPhoto, SizeVariantData } from "@/embed/types";
import type { FilmstripLayoutResult } from "@/embed/layouts/filmstrip";
import { createApiClient } from "@/embed/api";
import { layoutSquare } from "@/embed/layouts/square";
import { layoutMasonry } from "@/embed/layouts/masonry";
import { layoutGrid } from "@/embed/layouts/grid";
import { layoutJustified } from "@/embed/layouts/justified";
import { layoutFilmstrip, filmstripToLayoutResult } from "@/embed/layouts/filmstrip";
import Lightbox from "./Lightbox.vue";

interface Props {
	config: EmbedConfig;
}

const props = defineProps<Props>();

const loading = ref(true);
const error = ref<string | null>(null);
const albumData = ref<EmbedApiResponse | null>(null);
const gridContainer = ref<HTMLElement | null>(null);
const positionedPhotos = ref<PositionedPhoto[]>([]);
const containerHeight = ref(0);

// Filmstrip-specific state
const filmstripLayout = ref<FilmstripLayoutResult | null>(null);
const filmstripActiveIndex = ref(0);

// Lightbox state
const lightboxOpen = ref(false);
const lightboxPhotoIndex = ref(0);

// Computed property to check if we're in filmstrip mode
const isFilmstripMode = computed(() => props.config.layout === "filmstrip");

const containerStyle = computed(() => ({
	width: props.config.width,
	height: props.config.height === "auto" ? undefined : props.config.height,
}));

// Compute the gallery URL for the album
const galleryUrl = computed(() => {
	if (!albumData.value) return "";
	const mode = props.config.mode ?? "album";
	// For stream mode, link to the main gallery instead of a specific album
	if (mode === "stream") {
		return `${props.config.apiUrl}/gallery`;
	}
	return `${props.config.apiUrl}/gallery/${albumData.value.album.id}`;
});

/**
 * Get the best size variant for a photo based on its display size
 */
function getBestSizeVariant(photo: PositionedPhoto): string {
	const { width, height } = photo.position;
	const maxDimension = Math.max(width, height);

	// Select appropriate size variant based on display size
	const variants = photo.size_variants;

	// Helper to get URL from variant
	const getUrl = (variant: SizeVariantData | null): string | null => variant?.url ?? null;

	// Choose variant based on size (prefer 2x for retina displays)
	if (maxDimension <= 200) {
		return getUrl(variants.thumb2x) ?? getUrl(variants.thumb) ?? getUrl(variants.small) ?? "";
	} else if (maxDimension <= 400) {
		return getUrl(variants.small2x) ?? getUrl(variants.small) ?? getUrl(variants.medium) ?? "";
	} else if (maxDimension <= 600) {
		return getUrl(variants.medium2x) ?? getUrl(variants.medium) ?? getUrl(variants.small2x) ?? "";
	} else {
		return getUrl(variants.medium2x) ?? getUrl(variants.medium) ?? "";
	}
}

/**
 * Calculate layout based on selected algorithm
 */
function calculateLayout() {
	if (!albumData.value || !gridContainer.value) {
		return;
	}

	const containerWidth = gridContainer.value.clientWidth;
	// Limit photos based on maxPhotos config
	const maxPhotos = props.config.maxPhotos ?? 18;
	const photos = maxPhotos === "none" ? albumData.value.photos : albumData.value.photos.slice(0, maxPhotos);
	const spacing = props.config.spacing ?? 8;

	let result;

	switch (props.config.layout) {
		case "square":
			result = layoutSquare(photos, containerWidth, props.config.targetColumnWidth ?? 200, spacing);
			break;

		case "masonry":
			result = layoutMasonry(photos, containerWidth, props.config.targetColumnWidth ?? 300, spacing);
			break;

		case "grid":
			result = layoutGrid(photos, containerWidth, props.config.targetColumnWidth ?? 250, spacing);
			break;

		case "justified":
			result = layoutJustified(photos, containerWidth, props.config.targetRowHeight ?? 320, spacing);
			break;

		case "filmstrip":
			// Filmstrip needs container height and special handling
			const height = props.config.height === "auto" ? 600 : parseInt(props.config.height ?? "600");
			const filmstripResult = layoutFilmstrip(photos, containerWidth, height, 100, spacing, filmstripActiveIndex.value);
			filmstripLayout.value = filmstripResult;
			// For filmstrip, only set positioned photos to thumbnails for thumbnail strip
			result = filmstripToLayoutResult(filmstripResult);
			break;

		default:
			// Default to justified
			result = layoutJustified(photos, containerWidth, props.config.targetRowHeight ?? 320, spacing);
	}

	positionedPhotos.value = result.photos;
	containerHeight.value = result.containerHeight;
}

/**
 * Select a photo in filmstrip mode
 */
function selectFilmstripPhoto(index: number) {
	filmstripActiveIndex.value = index;
	calculateLayout();
}

/**
 * Open lightbox for photo
 */
function openLightbox(photoId: string) {
	if (!albumData.value) return;

	// Find photo index
	const index = albumData.value.photos.findIndex((p) => p.id === photoId);
	if (index !== -1) {
		lightboxPhotoIndex.value = index;
		lightboxOpen.value = true;
	}
}

/**
 * Close lightbox
 */
function closeLightbox() {
	lightboxOpen.value = false;
}

// Fetch album data on mount
onMounted(() => {
	const apiClient = createApiClient(props.config.apiUrl);
	const mode = props.config.mode ?? "album";

	let loadPromise: Promise<EmbedApiResponse>;

	if (mode === "stream") {
		// Fetch public stream
		const limit = props.config.maxPhotos === "none" ? 500 : props.config.maxPhotos;
		const sort = props.config.sortOrder ?? "desc";
		loadPromise = apiClient.fetchStream(limit, 0, sort, props.config.author).then((streamData) => ({
			album: {
				id: "",
				title: "Public Photo Stream",
				description: null,
				photo_count: streamData.photos.length,
				copyright: null,
				license: null,
			},
			photos: streamData.photos,
		}));
	} else {
		if (!props.config.albumId) {
			loadPromise = Promise.reject(new Error("Album ID is required for album mode"));
		} else {
			const limit = props.config.maxPhotos === "none" ? undefined : props.config.maxPhotos;
			const sort = props.config.sortOrder; // Pass undefined if not set (use album default)
			loadPromise = apiClient.fetchAlbum(props.config.albumId, limit, 0, sort, props.config.author);
		}
	}

	loadPromise
		.then((data) => {
			albumData.value = data;
			loading.value = false;
			return nextTick();
		})
		.then(() => {
			calculateLayout();
			window.addEventListener("resize", calculateLayout);
		})
		.catch((err) => {
			error.value = err instanceof Error ? err.message : "Failed to load album";
			loading.value = false;
		});
});

// Cleanup on unmount
onUnmounted(() => {
	window.removeEventListener("resize", calculateLayout);
});

// Recalculate layout when config changes
watch(
	() => [props.config.layout, props.config.spacing, props.config.targetRowHeight, props.config.targetColumnWidth],
	() => {
		if (albumData.value) {
			nextTick(() => calculateLayout());
		}
	},
);
</script>

<style scoped>
/* Base styles */
.lychee-embed {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
	box-sizing: border-box;
	/* Transparent background - adapts to host page */
	background-color: transparent;
	/* Inherit text color from host page */
	color: inherit;
}

.lychee-embed *,
.lychee-embed *::before,
.lychee-embed *::after {
	box-sizing: inherit;
}

/* Loading state */
.lychee-embed__loading {
	padding: 2em;
	text-align: center;
	font-size: 1em;
	opacity: 0.7;
}

/* Error state */
.lychee-embed__error {
	padding: 2em;
	text-align: center;
	color: #dc2626;
	background-color: rgba(220, 38, 38, 0.1);
	border: 1px solid rgba(220, 38, 38, 0.3);
	border-radius: 0.5em;
	margin: 1em;
}

/* Header */
.lychee-embed__header {
	padding: 1em 1.5em 0.5em 1.5em;
}

.lychee-embed__header-content {
	/* No special layout needed */
}

.lychee-embed__title {
	margin: 0 0 0.5em 0;
	font-size: 1.5em;
	font-weight: 600;
	line-height: 1.3;
	display: flex;
	align-items: center;
	gap: 0.5em;
}

.lychee-embed__description {
	margin: 0;
	font-size: 1em;
	opacity: 0.8;
	line-height: 1.5;
}

.lychee-embed__gallery-link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 1.75em;
	height: 1.75em;
	font-size: 1.25em;
	color: inherit;
	text-decoration: none;
	border-radius: 0.25em;
	transition:
		background-color 0.2s ease,
		opacity 0.2s ease;
	opacity: 0.5;
	flex-shrink: 0;
}

.lychee-embed__gallery-link:hover {
	opacity: 1;
	background-color: rgba(128, 128, 128, 0.1);
}

/* Photo grid */
.lychee-embed__grid {
	position: relative;
	width: 100%;
}

/* Filmstrip layout */
.lychee-embed__filmstrip {
	position: relative;
	width: 100%;
}

.lychee-embed__filmstrip-main {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
}

.lychee-embed__filmstrip-main-img {
	width: 100%;
	height: 100%;
	object-fit: contain;
	cursor: pointer;
}

.lychee-embed__filmstrip-nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	width: 3em;
	height: 3em;
	background: rgba(0, 0, 0, 0.5);
	border: none;
	border-radius: 50%;
	color: white;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10;
	transition: background-color 0.2s;
}

.lychee-embed__filmstrip-nav-icon {
	font-size: 2.5em;
	line-height: 1;
	color: white;
	font-weight: 300;
	display: block;
}

.lychee-embed__filmstrip-nav--prev .lychee-embed__filmstrip-nav-icon {
	transform: translate(-0.03em, -0.08em);
}

.lychee-embed__filmstrip-nav--next .lychee-embed__filmstrip-nav-icon {
	transform: translate(0.05em, -0.08em);
}

.lychee-embed__filmstrip-nav:hover {
	background: rgba(0, 0, 0, 0.8);
}

.lychee-embed__filmstrip-nav--prev {
	left: 1em;
}

.lychee-embed__filmstrip-nav--next {
	right: 1em;
}

.lychee-embed__filmstrip-thumbnails {
	overflow-x: auto;
	overflow-y: hidden;
}

.lychee-embed__filmstrip-thumb {
	cursor: pointer;
	outline: none;
	border: 2px solid transparent;
}

.lychee-embed__filmstrip-thumb--active {
	border-color: #38bdf8;
}

/* Footer (bottom placement) */
.lychee-embed__footer {
	padding: 1em 1.5em;
	text-align: center;
}

.lychee-embed__footer-link {
	display: inline-block;
	color: inherit;
	text-decoration: none;
	opacity: 0.7;
	font-size: 0.875em;
	transition: opacity 0.2s ease;
}

.lychee-embed__footer-link:hover {
	opacity: 1;
	text-decoration: underline;
}

/* Photo containers */
.lychee-embed__photo {
	overflow: hidden;
	cursor: pointer;
	transition: transform 0.2s ease;
	outline: none;
}

.lychee-embed__photo:hover,
.lychee-embed__photo:focus {
	transform: scale(1.02);
	z-index: 1;
}

.lychee-embed__photo:focus {
	outline: 2px solid #38bdf8;
	outline-offset: 2px;
}

/* Photo images */
.lychee-embed__photo-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}

/* Video play icon overlay */
.lychee-embed__video-overlay {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	pointer-events: none;
	transition: opacity 0.3s ease;
}

.lychee-embed__photo:hover .lychee-embed__video-overlay,
.lychee-embed__filmstrip-thumb:hover .lychee-embed__video-overlay,
.lychee-embed__filmstrip-main:hover .lychee-embed__video-overlay {
	opacity: 0.7;
}

.lychee-embed__play-icon {
	width: 20%;
	height: 20%;
	min-width: 40px;
	min-height: 40px;
	max-width: 80px;
	max-height: 80px;
	filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}
</style>
