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

			<!-- Photo grid placeholder -->
			<div class="lychee-embed__grid">
				<p>Photo grid will be implemented in next phase ({{ albumData.photos.length }} photos)</p>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import type { EmbedConfig, EmbedApiResponse } from "../types";
import { createApiClient } from "../api";

interface Props {
	config: EmbedConfig;
}

const props = defineProps<Props>();

const loading = ref(true);
const error = ref<string | null>(null);
const albumData = ref<EmbedApiResponse | null>(null);

const containerStyle = computed(() => ({
	width: props.config.width,
	height: props.config.height === "auto" ? undefined : props.config.height,
}));

onMounted(async () => {
	try {
		const apiClient = createApiClient(props.config.apiUrl);
		albumData.value = await apiClient.fetchAlbum(props.config.albumId);
		loading.value = false;
	} catch (err) {
		error.value = err instanceof Error ? err.message : "Failed to load album";
		loading.value = false;
	}
});
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

/* Grid placeholder */
.lychee-embed__grid {
	padding: 2rem;
}
</style>
