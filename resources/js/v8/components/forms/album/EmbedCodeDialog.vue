<template>
	<UModal v-model:open="is_embed_code_visible" :dismissible="true">
		<template #header>
			<span class="font-bold text-xl">{{
				config.mode === "stream" ? $t("dialogs.embed_code.title_stream") : $t("dialogs.embed_code.title")
			}}</span>
		</template>
		<template #body>
			<div class="inline-flex flex-col gap-4 w-full">
				<!-- Layout Selection -->
				<div class="flex flex-col gap-2">
					<label class="font-semibold">{{ $t("dialogs.embed_code.layout") }}</label>
					<UFieldGroup>
						<UButton
							v-for="option in layoutOptions"
							:key="option.value"
							size="sm"
							:color="config.layout === option.value ? 'primary' : 'neutral'"
							:variant="config.layout === option.value ? 'solid' : 'outline'"
							@click="config.layout = option.value"
						>
							{{ option.label }}
						</UButton>
					</UFieldGroup>
				</div>

				<!-- Advanced Options Toggle -->
				<UButton
					v-if="!showAdvanced"
					color="neutral"
					variant="ghost"
					icon="prime:angle-down"
					:label="$t('dialogs.embed_code.show_advanced')"
					@click="showAdvanced = true"
				/>

				<!-- Advanced Options -->
				<template v-if="showAdvanced">
					<UButton
						color="neutral"
						variant="ghost"
						icon="prime:angle-up"
						:label="$t('dialogs.embed_code.hide_advanced')"
						@click="showAdvanced = false"
					/>

					<div class="grid grid-cols-2 gap-4">
						<!-- Maximum Photos -->
						<UFormField :label="$t('dialogs.embed_code.max_photos')">
							<USelectMenu
								id="maxPhotos"
								v-model="config.maxPhotos"
								:items="maxPhotosOptions"
								value-key="value"
								label-key="label"
								class="w-full"
							/>
						</UFormField>

						<!-- Sort Order -->
						<UFormField :label="$t('dialogs.embed_code.sort_order')">
							<USelectMenu
								id="sortOrder"
								v-model="config.sortOrder"
								:items="sortOrderOptions"
								value-key="value"
								label-key="label"
								class="w-full"
							/>
						</UFormField>

						<!-- Spacing -->
						<UFormField :label="$t('dialogs.embed_code.spacing')">
							<UInputNumber id="spacing" v-model="config.spacing" :min="0" :max="50" class="w-full" />
						</UFormField>

						<!-- Target Row Height (for justified) -->
						<UFormField
							v-if="config.layout === 'justified' || config.layout === 'filmstrip'"
							:label="$t('dialogs.embed_code.row_height')"
						>
							<UInputNumber id="rowHeight" v-model="config.targetRowHeight" :min="100" :max="800" class="w-full" />
						</UFormField>

						<!-- Target Column Width (for grid/masonry/square) -->
						<UFormField v-if="['square', 'masonry', 'grid'].includes(config.layout)" :label="$t('dialogs.embed_code.column_width')">
							<UInputNumber id="columnWidth" v-model="config.targetColumnWidth" :min="100" :max="500" class="w-full" />
						</UFormField>
					</div>

					<!-- Header Placement Selection -->
					<div class="flex flex-col gap-2">
						<label class="font-semibold">{{ $t("dialogs.embed_code.header_placement") }}</label>
						<UFieldGroup>
							<UButton
								v-for="option in headerPlacementOptions"
								:key="option.value"
								size="sm"
								:color="config.headerPlacement === option.value ? 'primary' : 'neutral'"
								:variant="config.headerPlacement === option.value ? 'solid' : 'outline'"
								@click="config.headerPlacement = option.value"
							>
								{{ option.label }}
							</UButton>
						</UFieldGroup>
					</div>
				</template>

				<!-- Preview -->
				<div>
					<label class="font-semibold block mb-2">{{ $t("dialogs.embed_code.preview") }}</label>
					<div ref="previewContainer" class="border border-default rounded p-4 bg-elevated/50 h-64 overflow-auto"></div>
				</div>

				<!-- Generated Code -->
				<div>
					<label class="font-semibold block mb-2">{{ $t("dialogs.embed_code.code") }}</label>
					<UTextarea :model-value="generatedCode" readonly :rows="8" class="font-mono text-xs w-full" @focus="selectCode" />
				</div>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" color="neutral" variant="soft" @click="closeCallback">
					{{ $t("dialogs.button.close") }}
				</UButton>
				<UButton class="flex-1 justify-center" icon="prime:copy" @click="copyCode">
					{{ copied ? $t("dialogs.embed_code.copied") : $t("dialogs.embed_code.copy") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useAlbumStore } from "@/stores/AlbumState";
import Constants from "@/services/constants";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

// Type declaration for the embed widget global
declare global {
	interface Window {
		LycheeEmbed?: {
			createLycheeEmbed: (element: HTMLElement, config: Record<string, unknown>) => { unmount: () => void };
		};
	}
}

const togglableStore = useTogglablesStateStore();
const { is_embed_code_visible, embed_code_mode } = storeToRefs(togglableStore);
const albumStore = useAlbumStore();
const toast = useAppToast();

const previewContainer = ref<HTMLElement | null>(null);
const copied = ref(false);
const showAdvanced = ref(false);
const previewLoaded = ref(false);
const previewError = ref(false);

// Embed configuration
const config = ref({
	mode: embed_code_mode.value,
	layout: "justified" as "square" | "masonry" | "grid" | "justified" | "filmstrip",
	spacing: 8,
	targetRowHeight: 200,
	targetColumnWidth: 200,
	maxPhotos: 18 as number | "none",
	sortOrder: "desc" as "asc" | "desc",
	showTitle: true,
	showDescription: true,
	showCaptions: true,
	showExif: true,
	headerPlacement: "top" as "top" | "bottom" | "none",
});

// Layout options
const layoutOptions = computed<{ label: string; value: "square" | "masonry" | "grid" | "justified" | "filmstrip" }[]>(() => [
	{ label: trans("gallery.layout.squares"), value: "square" },
	{ label: trans("gallery.layout.masonry"), value: "masonry" },
	{ label: trans("gallery.layout.grid"), value: "grid" },
	{ label: trans("gallery.layout.justified"), value: "justified" },
	{ label: trans("gallery.layout.filmstrip"), value: "filmstrip" },
]);

// Header placement options
const headerPlacementOptions = computed<{ label: string; value: "top" | "bottom" | "none" }[]>(() => [
	{ label: trans("dialogs.embed_code.header_top"), value: "top" },
	{ label: trans("dialogs.embed_code.header_bottom"), value: "bottom" },
	{ label: trans("dialogs.embed_code.header_none"), value: "none" },
]);

// Max photos options
const maxPhotosOptions = computed(() => [
	{ label: trans("dialogs.embed_code.max_photos_none"), value: "none" },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 6), value: 6 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 12), value: 12 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 18), value: 18 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 30), value: 30 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 60), value: 60 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 90), value: 90 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 120), value: 120 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 180), value: 180 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 300), value: 300 },
	{ label: sprintf(trans("dialogs.embed_code.max_photos_count"), 500), value: 500 },
]);

// Sort order options
const sortOrderOptions = computed(() => [
	{ label: trans("dialogs.embed_code.sort_newest"), value: "desc" },
	{ label: trans("dialogs.embed_code.sort_oldest"), value: "asc" },
]);

// Get the base URL for the Lychee instance
const apiUrl = computed(() => {
	// Use the base URL from the HTML <base> tag, removing trailing slashes
	return Constants.BASE_URL.replace(/\/+$/, "");
});

// Get the album ID from the store
const albumId = computed(() => albumStore.album?.id ?? "");

// Widget version for cache busting and tracking
const EMBED_VERSION = "1.0.0";

// Generate embed code
const generatedCode = computed(() => {
	const embedUrl = `${apiUrl.value}/embed`;
	const albumIdAttr = config.value.mode === "album" ? `\n    data-album-id="${albumId.value}"` : "";
	const modeTitle = config.value.mode === "album" ? "Photo Album" : "Photo Stream";

	return `<!-- Lychee ${modeTitle} Embed v${EMBED_VERSION} -->
<link rel="stylesheet" href="${embedUrl}/lychee-embed.css?v=${EMBED_VERSION}">
<script src="${embedUrl}/lychee-embed.js?v=${EMBED_VERSION}"><\/script>

<div
    data-lychee-embed
    data-api-url="${apiUrl.value}"
    data-mode="${config.value.mode}"${albumIdAttr}
    data-layout="${config.value.layout}"
    data-spacing="${config.value.spacing}"
    data-target-row-height="${config.value.targetRowHeight}"
    data-target-column-width="${config.value.targetColumnWidth}"
    data-max-photos="${config.value.maxPhotos}"
    data-sort-order="${config.value.sortOrder}"
    data-header-placement="${config.value.headerPlacement}"
></div>`;
});

// Copy code to clipboard
function copyCode() {
	navigator.clipboard
		.writeText(generatedCode.value)
		.then(() => {
			copied.value = true;
			toast.add({
				severity: "success",
				summary: trans("dialogs.embed_code.copied"),
				detail: trans("dialogs.embed_code.copy_success"),
				life: 3000,
			});

			// Reset copied state after 3 seconds
			setTimeout(() => {
				copied.value = false;
			}, 3000);
		})
		.catch(() => {
			toast.add({
				severity: "error",
				summary: trans("dialogs.embed_code.copy_error"),
				detail: trans("dialogs.embed_code.copy_error_message"),
				life: 3000,
			});
		});
}

// Select all code when textarea is focused
function selectCode(event: Event) {
	(event.target as HTMLTextAreaElement).select();
}

// Load embed widget assets dynamically
function loadEmbedAssets() {
	return new Promise<void>((resolve, reject) => {
		// Check if already loaded
		if (previewLoaded.value) {
			resolve();
			return;
		}

		const embedUrl = `${apiUrl.value}/embed`;
		const cacheKey = Date.now(); // Cache busting

		// Load CSS
		const link = document.createElement("link");
		link.rel = "stylesheet";
		link.href = `${embedUrl}/lychee-embed.css?v=${cacheKey}`;
		document.head.appendChild(link);

		// Load JS
		const script = document.createElement("script");
		script.src = `${embedUrl}/lychee-embed.js?v=${cacheKey}`;
		script.onload = () => {
			previewLoaded.value = true;
			resolve();
		};
		script.onerror = () => {
			previewError.value = true;
			reject(new Error("Failed to load embed widget"));
		};
		document.head.appendChild(script);
	});
}

function closeCallback() {
	is_embed_code_visible.value = false;
}

// Initialize preview
function initializePreview() {
	// For album mode, we need an albumId. For stream mode, we don't.
	if (!previewContainer.value || (config.value.mode === "album" && !albumId.value)) {
		return;
	}

	// Clear previous content
	previewContainer.value.innerHTML = "";

	// Load assets if needed
	loadEmbedAssets()
		.then(() => {
			if (!previewContainer.value) {
				return;
			}

			// Create a container div for the widget
			const widgetContainer = document.createElement("div");
			widgetContainer.setAttribute("data-lychee-embed", "");
			widgetContainer.setAttribute("data-api-url", apiUrl.value);
			widgetContainer.setAttribute("data-mode", config.value.mode);
			if (config.value.mode === "album") {
				widgetContainer.setAttribute("data-album-id", albumId.value);
			}
			widgetContainer.setAttribute("data-layout", config.value.layout);
			widgetContainer.setAttribute("data-spacing", String(config.value.spacing));
			widgetContainer.setAttribute("data-target-row-height", String(config.value.targetRowHeight));
			widgetContainer.setAttribute("data-target-column-width", String(config.value.targetColumnWidth));
			widgetContainer.setAttribute("data-max-photos", String(config.value.maxPhotos));
			widgetContainer.setAttribute("data-sort-order", config.value.sortOrder);
			widgetContainer.setAttribute("data-header-placement", config.value.headerPlacement);
			widgetContainer.setAttribute("data-height", "200px"); // Fixed height for preview

			previewContainer.value.appendChild(widgetContainer);

			// Initialize the widget using the global LycheeEmbed
			if (window.LycheeEmbed && window.LycheeEmbed.createLycheeEmbed) {
				const widgetConfig: Record<string, unknown> = {
					apiUrl: apiUrl.value,
					mode: config.value.mode,
					layout: config.value.layout,
					spacing: config.value.spacing,
					targetRowHeight: config.value.targetRowHeight,
					targetColumnWidth: config.value.targetColumnWidth,
					maxPhotos: config.value.maxPhotos,
					sortOrder: config.value.sortOrder,
					showTitle: config.value.showTitle,
					showDescription: config.value.showDescription,
					showCaptions: config.value.showCaptions,
					showExif: config.value.showExif,
					headerPlacement: config.value.headerPlacement,
					height: "200px",
				};

				// Only add albumId for album mode
				if (config.value.mode === "album") {
					widgetConfig.albumId = albumId.value;
				}

				window.LycheeEmbed.createLycheeEmbed(widgetContainer, widgetConfig);
			}
		})
		.catch((error) => {
			console.error("Failed to initialize preview:", error);
			if (previewContainer.value) {
				previewContainer.value.innerHTML = `<div class="text-xs text-red-500 text-center p-4">${trans("dialogs.embed_code.preview_failed")}</div>`;
			}
		});
}

// Watch for config changes and reinitialize preview
watch(
	config,
	() => {
		if (is_embed_code_visible.value) {
			initializePreview();
		}
	},
	{ deep: true },
);

// Initialize preview when dialog opens
watch(is_embed_code_visible, (visible) => {
	if (visible) {
		// Sync mode from store
		config.value.mode = embed_code_mode.value;
		// Small delay to ensure DOM is ready
		setTimeout(() => {
			initializePreview();
		}, 100);
	} else {
		copied.value = false;
		showAdvanced.value = false;
		previewLoaded.value = false; // Reset to force reload with new cache key
		// Clear preview on close
		if (previewContainer.value) {
			previewContainer.value.innerHTML = "";
		}
	}
});
</script>
