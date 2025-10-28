<template>
	<div :class="['lychee-embed', `lychee-embed--${config.theme}`, config.containerClass]" :style="containerStyle">
		<div v-if="loading" class="lychee-embed__loading">Loading album...</div>

		<div v-else-if="error" class="lychee-embed__error">
			{{ error }}
		</div>

		<div v-else-if="albumData" class="lychee-embed__content">
			<!-- Album header -->
			<div v-if="config.showTitle || config.showDescription" class="lychee-embed__header">
				<h2 v-if="config.showTitle" class="lychee-embed__title">
					{{ albumData.album.title }}
				</h2>
				<p v-if="config.showDescription && albumData.album.description" class="lychee-embed__description">
					{{ albumData.album.description }}
				</p>
			</div>

			<!-- Photo grid with selected layout -->
			<div ref="gridContainer" class="lychee-embed__grid" :style="{ height: `${containerHeight}px` }">
				<div
					v-for="photo in positionedPhotos"
					:key="photo.id"
					class="lychee-embed__photo"
					:style="{
						position: 'absolute',
						top: `${photo.position.top}px`,
						left: `${photo.position.left}px`,
						width: `${photo.position.width}px`,
						height: `${photo.position.height}px`,
					}"
					@click="openLightbox(photo.id)"
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
	const photos = albumData.value.photos;
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
}

.lychee-embed *,
.lychee-embed *::before,
.lychee-embed *::after {
	box-sizing: inherit;
}

/* Light theme */
.lychee-embed--light {
	background-color: #ffffff;
	color: #333333;
}

/* Dark theme */
.lychee-embed--dark {
	background-color: #1a1a1a;
	color: #e0e0e0;
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
	background-color: #fee2e2;
	border-radius: 0.5rem;
	margin: 1rem;
}

.lychee-embed--dark .lychee-embed__error {
	color: #fca5a5;
	background-color: #7f1d1d;
}

/* Header */
.lychee-embed__header {
	padding: 1.5rem;
	border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.lychee-embed--dark .lychee-embed__header {
	border-bottom-color: rgba(255, 255, 255, 0.1);
}

.lychee-embed__title {
	margin: 0 0 0.5rem 0;
	font-size: 1.5rem;
	font-weight: 600;
	line-height: 1.3;
}

.lychee-embed__description {
	margin: 0;
	font-size: 1rem;
	opacity: 0.8;
	line-height: 1.5;
}

/* Photo grid */
.lychee-embed__grid {
	position: relative;
	width: 100%;
}

/* Photo containers */
.lychee-embed__photo {
	overflow: hidden;
	cursor: pointer;
	transition: transform 0.2s ease;
}

.lychee-embed__photo:hover {
	transform: scale(1.02);
	z-index: 1;
}

/* Photo images */
.lychee-embed__photo-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}
</style>
