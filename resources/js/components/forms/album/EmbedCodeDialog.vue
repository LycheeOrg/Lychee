<template>
	<Dialog v-model:visible="is_embed_code_visible" pt:root:class="border-none" modal :dismissable-mask="true" @close="is_embed_code_visible = false">
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative text-sm w-full md:w-2xl rounded-md pt-9">
				<h2 class="mb-5 px-9 text-2xl font-bold">{{ $t("dialogs.embed_code.title") }}</h2>
				<p class="mb-5 px-9 text-muted-color">{{ $t("dialogs.embed_code.info") }}</p>

				<!-- Configuration Options -->
				<div class="inline-flex flex-col gap-4 px-9 mb-6">
					<!-- Layout Selection -->
					<div class="flex flex-col gap-2">
						<label class="font-semibold">{{ $t("dialogs.embed_code.layout") }}</label>
						<SelectButton v-model="config.layout" :options="layoutOptions" option-label="label" option-value="value" />
					</div>

					<!-- Advanced Options Toggle -->
					<Button
						v-if="!showAdvanced"
						severity="secondary"
						text
						icon="pi pi-angle-down"
						:label="$t('dialogs.embed_code.show_advanced')"
						@click="showAdvanced = true"
					/>

					<!-- Advanced Options -->
					<template v-if="showAdvanced">
						<Button
							severity="secondary"
							text
							icon="pi pi-angle-up"
							:label="$t('dialogs.embed_code.hide_advanced')"
							@click="showAdvanced = false"
						/>

						<div class="grid grid-cols-2 gap-4">
							<!-- Maximum Photos -->
							<FloatLabel variant="on">
								<InputNumber id="maxPhotos" v-model="config.maxPhotos" :min="1" :max="100" />
								<label for="maxPhotos">{{ $t("dialogs.embed_code.max_photos") }}</label>
							</FloatLabel>

							<!-- Spacing -->
							<FloatLabel variant="on">
								<InputNumber id="spacing" v-model="config.spacing" :min="0" :max="50" suffix=" px" />
								<label for="spacing">{{ $t("dialogs.embed_code.spacing") }}</label>
							</FloatLabel>

							<!-- Target Row Height (for justified) -->
							<FloatLabel variant="on" v-if="config.layout === 'justified' || config.layout === 'filmstrip'">
								<InputNumber id="rowHeight" v-model="config.targetRowHeight" :min="100" :max="800" suffix=" px" />
								<label for="rowHeight">{{ $t("dialogs.embed_code.row_height") }}</label>
							</FloatLabel>

							<!-- Target Column Width (for grid/masonry/square) -->
							<FloatLabel variant="on" v-if="['square', 'masonry', 'grid'].includes(config.layout)">
								<InputNumber id="columnWidth" v-model="config.targetColumnWidth" :min="100" :max="500" suffix=" px" />
								<label for="columnWidth">{{ $t("dialogs.embed_code.column_width") }}</label>
							</FloatLabel>
						</div>

						<!-- Display Options -->
						<div class="flex flex-col gap-2">
							<label class="font-semibold">{{ $t("dialogs.embed_code.display_options") }}</label>
							<div class="flex items-center gap-2">
								<Checkbox v-model="config.showTitle" input-id="showTitle" binary />
								<label for="showTitle">{{ $t("dialogs.embed_code.show_title") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<Checkbox v-model="config.showDescription" input-id="showDescription" binary />
								<label for="showDescription">{{ $t("dialogs.embed_code.show_description") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<Checkbox v-model="config.showCaptions" input-id="showCaptions" binary />
								<label for="showCaptions">{{ $t("dialogs.embed_code.show_captions") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<Checkbox v-model="config.showExif" input-id="showExif" binary />
								<label for="showExif">{{ $t("dialogs.embed_code.show_exif") }}</label>
							</div>
						</div>

						<!-- Theme Selection -->
						<div class="flex flex-col gap-2">
							<label class="font-semibold">{{ $t("dialogs.embed_code.theme") }}</label>
							<SelectButton v-model="config.theme" :options="themeOptions" option-label="label" option-value="value" />
						</div>
					</template>
				</div>

				<!-- Preview -->
				<div class="px-9 mb-6">
					<label class="font-semibold block mb-2">{{ $t("dialogs.embed_code.preview") }}</label>
					<div
						ref="previewContainer"
						class="border border-surface-border rounded p-4 bg-surface-50 dark:bg-surface-800 h-64 overflow-auto"
					></div>
				</div>

				<!-- Generated Code -->
				<div class="px-9 mb-6">
					<label class="font-semibold block mb-2">{{ $t("dialogs.embed_code.code") }}</label>
					<Textarea
						ref="codeTextarea"
						:model-value="generatedCode"
						readonly
						rows="8"
						class="font-mono text-xs w-full"
						@focus="selectCode"
					/>
				</div>

				<!-- Action Buttons -->
				<div class="flex items-center">
					<Button severity="secondary" class="w-full font-bold border-none rounded-bl-xl" @click="closeCallback">
						{{ $t("dialogs.button.close") }}
					</Button>
					<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="copyCode">
						<i class="pi pi-copy mr-2" />
						{{ copied ? $t("dialogs.embed_code.copied") : $t("dialogs.embed_code.copy") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import InputNumber from "primevue/inputnumber";
import FloatLabel from "primevue/floatlabel";
import Checkbox from "primevue/checkbox";
import Textarea from "primevue/textarea";
import { computed, ref, watch } from "vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { useToast } from "primevue/usetoast";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLycheeStateStore } from "@/stores/LycheeState";

// Type declaration for the embed widget global
declare global {
	interface Window {
		LycheeEmbed?: {
			createLycheeEmbed: (element: HTMLElement, config: any) => any;
		};
	}
}

const togglableStore = useTogglablesStateStore();
const { is_embed_code_visible } = storeToRefs(togglableStore);
const albumStore = useAlbumStore();
const lycheeStore = useLycheeStateStore();
const toast = useToast();

const codeTextarea = ref<InstanceType<typeof Textarea> | null>(null);
const previewContainer = ref<HTMLElement | null>(null);
const copied = ref(false);
const showAdvanced = ref(false);
const previewLoaded = ref(false);
const previewError = ref(false);

// Embed configuration
const config = ref({
	layout: "justified" as "square" | "masonry" | "grid" | "justified" | "filmstrip",
	spacing: 8,
	targetRowHeight: 200,
	targetColumnWidth: 200,
	maxPhotos: 15,
	showTitle: true,
	showDescription: true,
	showCaptions: true,
	showExif: true,
	theme: "light" as "light" | "dark",
});

// Layout options
const layoutOptions = [
	{ label: "Square", value: "square" },
	{ label: "Masonry", value: "masonry" },
	{ label: "Grid", value: "grid" },
	{ label: "Justified", value: "justified" },
	{ label: "Filmstrip", value: "filmstrip" },
];

// Theme options
const themeOptions = [
	{ label: "Light", value: "light" },
	{ label: "Dark", value: "dark" },
];

// Get the base URL for the Lychee instance
const apiUrl = computed(() => {
	// Use window.location.origin for the current domain
	return window.location.origin;
});

// Get the album ID from the store
const albumId = computed(() => albumStore.album?.id ?? "");

// Generate embed code
const generatedCode = computed(() => {
	const embedUrl = `${apiUrl.value}/embed`;

	return `<!-- Lychee Photo Album Embed -->
<link rel="stylesheet" href="${embedUrl}/lychee-embed.css">
<script src="${embedUrl}/lychee-embed.js"><\/script>

<div
    data-lychee-embed
    data-api-url="${apiUrl.value}"
    data-album-id="${albumId.value}"
    data-layout="${config.value.layout}"
    data-spacing="${config.value.spacing}"
    data-target-row-height="${config.value.targetRowHeight}"
    data-target-column-width="${config.value.targetColumnWidth}"
    data-max-photos="${config.value.maxPhotos}"
    data-show-title="${config.value.showTitle}"
    data-show-description="${config.value.showDescription}"
    data-show-captions="${config.value.showCaptions}"
    data-show-exif="${config.value.showExif}"
    data-theme="${config.value.theme}"
></div>`;
});

// Copy code to clipboard
async function copyCode() {
	try {
		await navigator.clipboard.writeText(generatedCode.value);
		copied.value = true;
		toast.add({
			severity: "success",
			summary: "Copied",
			detail: "Embed code copied to clipboard",
			life: 3000,
		});

		// Reset copied state after 3 seconds
		setTimeout(() => {
			copied.value = false;
		}, 3000);
	} catch (error) {
		toast.add({
			severity: "error",
			summary: "Error",
			detail: "Failed to copy to clipboard",
			life: 3000,
		});
	}
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

// Initialize preview
async function initializePreview() {
	if (!previewContainer.value || !albumId.value) {
		return;
	}

	try {
		// Clear previous content
		previewContainer.value.innerHTML = "";

		// Load assets if needed
		await loadEmbedAssets();

		// Create a container div for the widget
		const widgetContainer = document.createElement("div");
		widgetContainer.setAttribute("data-lychee-embed", "");
		widgetContainer.setAttribute("data-api-url", apiUrl.value);
		widgetContainer.setAttribute("data-album-id", albumId.value);
		widgetContainer.setAttribute("data-layout", config.value.layout);
		widgetContainer.setAttribute("data-spacing", String(config.value.spacing));
		widgetContainer.setAttribute("data-target-row-height", String(config.value.targetRowHeight));
		widgetContainer.setAttribute("data-target-column-width", String(config.value.targetColumnWidth));
		widgetContainer.setAttribute("data-max-photos", String(config.value.maxPhotos));
		widgetContainer.setAttribute("data-show-title", String(config.value.showTitle));
		widgetContainer.setAttribute("data-show-description", String(config.value.showDescription));
		widgetContainer.setAttribute("data-show-captions", String(config.value.showCaptions));
		widgetContainer.setAttribute("data-show-exif", String(config.value.showExif));
		widgetContainer.setAttribute("data-theme", config.value.theme);
		widgetContainer.setAttribute("data-height", "200px"); // Fixed height for preview

		previewContainer.value.appendChild(widgetContainer);

		// Initialize the widget using the global LycheeEmbed
		if (window.LycheeEmbed && window.LycheeEmbed.createLycheeEmbed) {
			window.LycheeEmbed.createLycheeEmbed(widgetContainer, {
				apiUrl: apiUrl.value,
				albumId: albumId.value,
				layout: config.value.layout,
				spacing: config.value.spacing,
				targetRowHeight: config.value.targetRowHeight,
				targetColumnWidth: config.value.targetColumnWidth,
				showTitle: config.value.showTitle,
				showDescription: config.value.showDescription,
				showCaptions: config.value.showCaptions,
				showExif: config.value.showExif,
				theme: config.value.theme,
				height: "200px",
			});
		}
	} catch (error) {
		console.error("Failed to initialize preview:", error);
		if (previewContainer.value) {
			previewContainer.value.innerHTML =
				'<div class="text-xs text-red-500 text-center p-4">Failed to load preview. The embed widget will work when deployed.</div>';
		}
	}
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
