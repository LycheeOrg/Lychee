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
					<div class="border border-surface-border rounded p-4 bg-surface-50 dark:bg-surface-800 h-64 overflow-auto">
						<div class="text-xs text-muted-color text-center">
							{{ $t("dialogs.embed_code.preview_placeholder") }}
						</div>
					</div>
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

const togglableStore = useTogglablesStateStore();
const { is_embed_code_visible } = storeToRefs(togglableStore);
const albumStore = useAlbumStore();
const lycheeStore = useLycheeStateStore();
const toast = useToast();

const codeTextarea = ref<InstanceType<typeof Textarea> | null>(null);
const copied = ref(false);
const showAdvanced = ref(false);

// Embed configuration
const config = ref({
	layout: "justified" as "square" | "masonry" | "grid" | "justified" | "filmstrip",
	spacing: 8,
	targetRowHeight: 320,
	targetColumnWidth: 200,
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

// Reset copied state when dialog is closed
watch(is_embed_code_visible, (visible) => {
	if (!visible) {
		copied.value = false;
		showAdvanced.value = false;
	}
});
</script>
