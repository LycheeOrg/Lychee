<template>
	<div :class="['lychee-embed', config.containerClass]" :style="containerStyle">
		<div v-if="loading" class="lychee-embed__loading">Loading album...</div>

		<div v-else-if="error" class="lychee-embed__error">
			{{ error }}
		</div>

		<div v-else-if="albumData" class="lychee-embed__content">
			<!-- Album header (top placement) -->
			<div v-if="config.headerPlacement === 'top' && (config.showTitle || config.showDescription)" class="lychee-embed__header">
				<div class="lychee-embed__header-content">
					<h2 v-if="config.showTitle" class="lychee-embed__title">
						{{ albumData.album.title }}
						<a
							:href="galleryUrl"
							target="_blank"
							rel="noopener noreferrer"
							class="lychee-embed__gallery-link"
							title="View in Lychee Gallery"
						>
							↗
						</a>
					</h2>
					<p v-if="config.showDescription && albumData.album.description" class="lychee-embed__description">
						{{ albumData.album.description }}
					</p>
				</div>
			</div>

			<!-- Photo grid with selected layout -->
			<div ref="gridContainer" class="lychee-embed__grid" :style="{ height: `${containerHeight}px` }">
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
				</div>
			</div>

			<!-- Album link (bottom placement) -->
			<div v-if="config.headerPlacement === 'bottom'" class="lychee-embed__footer">
				<a
					:href="galleryUrl"
					target="_blank"
					rel="noopener noreferrer"
					class="lychee-embed__footer-link"
				>
					View "{{ albumData.album.title }}" in Lychee Gallery ↗
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
import type { EmbedConfig, EmbedApiResponse, PositionedPhoto, Photo, SizeVariantData } from "../types";
import { createApiClient } from "../api";
import { layoutSquare } from "../layouts/square";
import { layoutMasonry } from "../layouts/masonry";
import { layoutGrid } from "../layouts/grid";
import { layoutJustified } from "../layouts/justified";
import { layoutFilmstrip, filmstripToLayoutResult } from "../layouts/filmstrip";
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

// Lightbox state
const lightboxOpen = ref(false);
const lightboxPhotoIndex = ref(0);

const containerStyle = computed(() => ({
	width: props.config.width,
	height: props.config.height === "auto" ? undefined : props.config.height,
}));

// Compute the gallery URL for the album
const galleryUrl = computed(() => {
	if (!albumData.value) return "";
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
	const maxPhotos = props.config.maxPhotos ?? 15;
	const photos = albumData.value.photos.slice(0, maxPhotos);
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
			// Filmstrip needs container height
			const height = props.config.height === "auto" ? 600 : parseInt(props.config.height ?? "600");
			const filmstripResult = layoutFilmstrip(photos, containerWidth, height, 100, spacing);
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
onMounted(async () => {
	try {
		const apiClient = createApiClient(props.config.apiUrl);
		albumData.value = await apiClient.fetchAlbum(props.config.albumId);
		loading.value = false;

		// Calculate layout after data loads
		await nextTick();
		calculateLayout();

		// Recalculate on window resize
		window.addEventListener("resize", calculateLayout);
	} catch (err) {
		error.value = err instanceof Error ? err.message : "Failed to load album";
		loading.value = false;
	}
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
	padding: 2rem;
	text-align: center;
	font-size: 1rem;
	opacity: 0.7;
}

/* Error state */
.lychee-embed__error {
	padding: 2rem;
	text-align: center;
	color: #dc2626;
	background-color: rgba(220, 38, 38, 0.1);
	border: 1px solid rgba(220, 38, 38, 0.3);
	border-radius: 0.5rem;
	margin: 1rem;
}

/* Header */
.lychee-embed__header {
	padding: 1.5rem;
}

.lychee-embed__header-content {
	/* No special layout needed */
}

.lychee-embed__title {
	margin: 0 0 0.5rem 0;
	font-size: 1.5rem;
	font-weight: 600;
	line-height: 1.3;
	display: flex;
	align-items: center;
	gap: 0.5rem;
}

.lychee-embed__description {
	margin: 0;
	font-size: 1rem;
	opacity: 0.8;
	line-height: 1.5;
}

.lychee-embed__gallery-link {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 1.75rem;
	height: 1.75rem;
	font-size: 1.25rem;
	color: inherit;
	text-decoration: none;
	border-radius: 0.25rem;
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

/* Footer (bottom placement) */
.lychee-embed__footer {
	padding: 1rem 1.5rem;
	text-align: center;
}

.lychee-embed__footer-link {
	display: inline-block;
	color: inherit;
	text-decoration: none;
	opacity: 0.7;
	font-size: 0.875rem;
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
	outline: 2px solid #0066cc;
	outline-offset: 2px;
}

/* Photo images */
.lychee-embed__photo-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}
</style>
