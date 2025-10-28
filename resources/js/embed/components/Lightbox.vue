<template>
	<Teleport to="body">
		<Transition name="lightbox-fade">
			<div v-if="isOpen" ref="overlayRef" class="lightbox-overlay" @click="handleOverlayClick" @keydown="handleKeydown" tabindex="0">
				<div class="lightbox-container">
					<!-- Close button -->
					<button class="lightbox-close" @click="close" aria-label="Close" title="Close (Esc)">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>

					<!-- Navigation -->
					<button
						v-if="canGoPrevious"
						class="lightbox-nav lightbox-nav--prev"
						@click.stop="previous"
						aria-label="Previous photo"
						title="Previous (←)"
					>
						<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="15 18 9 12 15 6"></polyline>
						</svg>
					</button>

					<button v-if="canGoNext" class="lightbox-nav lightbox-nav--next" @click.stop="next" aria-label="Next photo" title="Next (→)">
						<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="9 18 15 12 9 6"></polyline>
						</svg>
					</button>

					<!-- Photo display -->
					<div class="lightbox-content">
						<div class="lightbox-image-container">
							<img
								v-if="currentPhoto"
								:src="getPhotoUrl(currentPhoto)"
								:alt="currentPhoto.title || 'Photo'"
								class="lightbox-image"
								@load="handleImageLoad"
								@error="handleImageError"
								@click.stop="cycleInfoMode"
								:title="'Click to cycle info display'"
							/>
							<div v-if="loading" class="lightbox-loading">Loading...</div>
							<div v-if="error" class="lightbox-error">{{ error }}</div>
						</div>

						<!-- Photo info -->
						<div v-if="currentPhoto && showAnyInfo" class="lightbox-info">
							<div class="lightbox-info-content">
								<h3 v-if="currentPhoto.title && showTitle" class="lightbox-title">{{ currentPhoto.title }}</h3>
								<p v-if="currentPhoto.description && showTitle" class="lightbox-description">{{ currentPhoto.description }}</p>

								<!-- EXIF data -->
								<div v-if="showExif" class="lightbox-exif">
									<div v-if="currentPhoto.exif.make || currentPhoto.exif.model" class="lightbox-exif-item">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
											<circle cx="12" cy="13" r="4"></circle>
										</svg>
										<span>{{ [currentPhoto.exif.make, currentPhoto.exif.model].filter(Boolean).join(" ") }}</span>
									</div>

									<div v-if="currentPhoto.exif.lens" class="lightbox-exif-item">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="10"></circle>
											<circle cx="12" cy="12" r="3"></circle>
										</svg>
										<span>{{ currentPhoto.exif.lens }}</span>
									</div>

									<div v-if="currentPhoto.exif.focal" class="lightbox-exif-item">
										<span class="lightbox-exif-label">{{ currentPhoto.exif.focal }}</span>
										<span v-if="currentPhoto.exif.aperture" class="lightbox-exif-label">f/{{ currentPhoto.exif.aperture }}</span>
										<span v-if="currentPhoto.exif.shutter" class="lightbox-exif-label">{{ currentPhoto.exif.shutter }}</span>
										<span v-if="currentPhoto.exif.iso" class="lightbox-exif-label">ISO {{ currentPhoto.exif.iso }}</span>
									</div>

									<div v-if="currentPhoto.exif.taken_at" class="lightbox-exif-item">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="10"></circle>
											<polyline points="12 6 12 12 16 14"></polyline>
										</svg>
										<span>{{ formatDate(currentPhoto.exif.taken_at) }}</span>
									</div>
								</div>

								<!-- Photo counter -->
								<div class="lightbox-counter">{{ currentIndex + 1 }} / {{ photos.length }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import type { Photo, EmbedConfig } from "../types";

interface Props {
	photos: Photo[];
	initialIndex: number;
	isOpen: boolean;
	config: EmbedConfig;
}

interface Emits {
	(e: "close"): void;
	(e: "update:currentIndex", index: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const currentIndex = ref(props.initialIndex);
const loading = ref(false);
const error = ref<string | null>(null);
const overlayRef = ref<HTMLElement | null>(null);

// Info display mode: 0 = none, 1 = title only, 2 = title + EXIF
const infoMode = ref(2); // Start with full info displayed

const currentPhoto = computed(() => props.photos[currentIndex.value] || null);
const canGoPrevious = computed(() => currentIndex.value > 0);
const canGoNext = computed(() => currentIndex.value < props.photos.length - 1);

const hasExif = computed(() => {
	if (!currentPhoto.value) return false;
	const exif = currentPhoto.value.exif;
	return !!(exif.make || exif.model || exif.lens || exif.focal || exif.aperture || exif.shutter || exif.iso || exif.taken_at);
});

// Computed properties for info display based on mode
const showAnyInfo = computed(() => infoMode.value > 0 && props.config.showCaptions);
const showTitle = computed(() => infoMode.value >= 1 && props.config.showCaptions);
const showExif = computed(() => infoMode.value >= 2 && props.config.showExif && hasExif.value);

/**
 * Cycle through info display modes
 */
function cycleInfoMode() {
	// Cycle: 0 (none) -> 1 (title) -> 2 (title+exif) -> 0 (none) ...
	infoMode.value = (infoMode.value + 1) % 3;
}

/**
 * Get the best photo URL for lightbox display
 */
function getPhotoUrl(photo: Photo): string {
	const variants = photo.size_variants;
	// Prefer medium2x or medium for lightbox
	return (
		variants.medium2x?.url ||
		variants.medium?.url ||
		variants.small2x?.url ||
		variants.small?.url ||
		variants.thumb2x?.url ||
		variants.thumb?.url ||
		""
	);
}

/**
 * Format date for display
 */
function formatDate(dateString: string): string {
	try {
		const date = new Date(dateString);
		return date.toLocaleDateString(undefined, {
			year: "numeric",
			month: "long",
			day: "numeric",
			hour: "2-digit",
			minute: "2-digit",
		});
	} catch {
		return dateString;
	}
}

/**
 * Navigate to previous photo
 */
function previous() {
	if (canGoPrevious.value) {
		currentIndex.value--;
		loading.value = true;
		error.value = null;
	}
}

/**
 * Navigate to next photo
 */
function next() {
	if (canGoNext.value) {
		currentIndex.value++;
		loading.value = true;
		error.value = null;
	}
}

/**
 * Close lightbox
 */
function close() {
	emit("close");
}

/**
 * Handle overlay click (close on click outside)
 */
function handleOverlayClick(event: MouseEvent) {
	if ((event.target as HTMLElement).classList.contains("lightbox-overlay")) {
		close();
	}
}

/**
 * Handle keyboard navigation
 */
function handleKeydown(event: KeyboardEvent) {
	switch (event.key) {
		case "Escape":
			close();
			break;
		case "ArrowLeft":
			previous();
			break;
		case "ArrowRight":
			next();
			break;
	}
}

/**
 * Handle image load
 */
function handleImageLoad() {
	loading.value = false;
	error.value = null;
}

/**
 * Handle image error
 */
function handleImageError() {
	loading.value = false;
	error.value = "Failed to load image";
}

// Watch for index changes from props
watch(
	() => props.initialIndex,
	(newIndex) => {
		currentIndex.value = newIndex;
		loading.value = true;
		error.value = null;
	},
);

// Emit index changes
watch(currentIndex, (newIndex) => {
	emit("update:currentIndex", newIndex);
});

// Focus overlay when opened
watch(
	() => props.isOpen,
	(isOpen) => {
		if (isOpen) {
			// Prevent body scroll
			document.body.style.overflow = "hidden";

			// Focus overlay for keyboard navigation
			setTimeout(() => {
				overlayRef.value?.focus();
			}, 100);
		} else {
			// Restore body scroll
			document.body.style.overflow = "";
		}
	},
);

// Cleanup on unmount
onUnmounted(() => {
	document.body.style.overflow = "";
});
</script>

<style scoped>
/* Overlay */
.lightbox-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.95);
	z-index: 9999;
	display: flex;
	align-items: center;
	justify-content: center;
	outline: none;
}

/* Container */
.lightbox-container {
	position: relative;
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
}

/* Close button */
.lightbox-close {
	position: absolute;
	top: 1rem;
	right: 1rem;
	width: 3rem;
	height: 3rem;
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

.lightbox-close:hover {
	background: rgba(0, 0, 0, 0.8);
}

/* Navigation buttons */
.lightbox-nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	width: 4rem;
	height: 4rem;
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

.lightbox-nav:hover {
	background: rgba(0, 0, 0, 0.8);
}

.lightbox-nav--prev {
	left: 2rem;
}

.lightbox-nav--next {
	right: 2rem;
}

/* Content */
.lightbox-content {
	display: flex;
	flex-direction: column;
	width: 90%;
	height: 90%;
	max-width: 1400px;
}

/* Image container */
.lightbox-image-container {
	flex: 1;
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;
	min-height: 0;
}

.lightbox-image {
	max-width: 100%;
	max-height: 100%;
	object-fit: contain;
	display: block;
	cursor: pointer;
}

/* Loading/Error states */
.lightbox-loading,
.lightbox-error {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	color: white;
	font-size: 1.2rem;
}

.lightbox-error {
	color: #ff6b6b;
}

/* Info panel */
.lightbox-info {
	margin-top: 1rem;
	color: white;
	max-height: 30%;
	overflow-y: auto;
}

.lightbox-info-content {
	padding: 1rem;
	background: rgba(0, 0, 0, 0.3);
	border-radius: 0.5rem;
}

.lightbox-title {
	margin: 0 0 0.5rem 0;
	font-size: 1.25rem;
	font-weight: 600;
}

.lightbox-description {
	margin: 0 0 1rem 0;
	font-size: 1rem;
	opacity: 0.9;
}

/* EXIF data */
.lightbox-exif {
	display: flex;
	flex-wrap: wrap;
	gap: 1rem;
	margin-top: 1rem;
	padding-top: 1rem;
	border-top: 1px solid rgba(255, 255, 255, 0.2);
	font-size: 0.9rem;
	opacity: 0.8;
}

.lightbox-exif-item {
	display: flex;
	align-items: center;
	gap: 0.5rem;
}

.lightbox-exif-label {
	margin-right: 0.5rem;
}

.lightbox-exif-label:not(:last-child)::after {
	content: "·";
	margin-left: 0.5rem;
	opacity: 0.5;
}

/* Counter */
.lightbox-counter {
	margin-top: 1rem;
	text-align: center;
	font-size: 0.9rem;
	opacity: 0.7;
}

/* Transitions */
.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
	transition: opacity 0.3s ease;
}

.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
	opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
	.lightbox-nav {
		width: 3rem;
		height: 3rem;
	}

	.lightbox-nav--prev {
		left: 1rem;
	}

	.lightbox-nav--next {
		right: 1rem;
	}

	.lightbox-close {
		width: 2.5rem;
		height: 2.5rem;
	}

	.lightbox-exif {
		font-size: 0.8rem;
	}
}
</style>
