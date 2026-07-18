<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("watermark.preview.title") }}
		<template #right>
			<UButton :label="$t('watermark.preview.save')" :loading="saving" icon="lucide:save" color="neutral" variant="ghost" @click="save" />
		</template>
	</UHeader>

	<!-- SE / SE Preview gate -->
	<div v-if="!isSeAvailable" class="max-w-5xl mx-auto mt-8 px-4 text-center">
		<UIcon name="lucide:lock" class="text-5xl text-muted mb-4" />
		<p class="text-muted">{{ $t("watermark.preview.se_required") }}</p>
	</div>

	<div v-else class="max-w-7xl mx-auto mt-4 px-4 pb-10 flex flex-col xl:flex-row gap-6">
		<!-- ─────────────── LEFT: Settings Panel ─────────────── -->
		<div class="w-full xl:w-96 shrink-0 flex flex-col gap-4">
			<!-- Watermark settings -->
			<Fieldset :legend="$t('watermark.preview.section_settings')">
				<div class="flex flex-col gap-4">
					<!-- Watermark photo ID -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.watermark_photo_id") }}</label>
						<div class="flex gap-2">
							<UInput
								v-model="watermarkPhotoId"
								:placeholder="$t('watermark.preview.watermark_photo_id_placeholder')"
								class="flex-1 text-sm"
								@keydown.enter="loadWatermarkImage"
							/>
							<UButton icon="lucide:refresh-cw" color="neutral" variant="ghost" @click="loadWatermarkImage" />
						</div>
						<small class="text-muted text-xs">{{ $t("watermark.preview.watermark_photo_id_hint") }}</small>
						<small v-if="watermarkLoadError" class="text-error text-xs">{{ $t("watermark.preview.watermark_load_error") }}</small>
					</div>

					<!-- Size slider -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.size", { value: watermarkSize }) }}</label>
						<input v-model.number="watermarkSize" type="range" min="1" max="100" class="w-full accent-primary-500" />
					</div>

					<!-- Opacity slider -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.opacity", { value: watermarkOpacity }) }}</label>
						<input v-model.number="watermarkOpacity" type="range" min="1" max="100" class="w-full accent-primary-500" />
					</div>

					<!-- Position grid -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.position") }}</label>
						<div class="grid grid-cols-3 gap-1">
							<UButton
								v-for="pos in positionOptions"
								:key="pos.value"
								:label="pos.label"
								size="xs"
								:color="watermarkPosition === pos.value ? 'primary' : 'neutral'"
								:variant="watermarkPosition === pos.value ? 'solid' : 'ghost'"
								class="text-xs"
								@click="watermarkPosition = pos.value"
							/>
						</div>
					</div>
				</div>
			</Fieldset>

			<!-- Preview background photo -->
			<Fieldset :legend="$t('watermark.preview.section_preview_photo')">
				<div class="flex flex-col gap-2">
					<label class="text-sm font-medium">{{ $t("watermark.preview.preview_photo_id") }}</label>
					<div class="flex gap-2">
						<UInput
							v-model="previewPhotoId"
							:placeholder="$t('watermark.preview.preview_photo_id_placeholder')"
							class="flex-1 text-sm"
							@keydown.enter="loadPreviewPhoto"
						/>
						<UButton icon="lucide:refresh-cw" color="neutral" variant="ghost" @click="loadPreviewPhoto" />
					</div>
					<small class="text-muted text-xs">{{ $t("watermark.preview.preview_photo_id_hint") }}</small>
					<small v-if="previewLoadError" class="text-error text-xs">{{ $t("watermark.preview.photo_load_error") }}</small>
				</div>
			</Fieldset>
		</div>

		<!-- ─────────────── RIGHT: Live Preview ─────────────── -->
		<div class="flex-1">
			<Fieldset :legend="$t('watermark.preview.section_preview')" class="h-full">
				<!-- No watermark image configured -->
				<div v-if="!watermarkImageUrl" class="flex flex-col items-center justify-center py-16 text-muted gap-3">
					<UIcon name="lucide:image" class="text-4xl" />
					<p class="text-sm text-center">{{ $t("watermark.preview.no_watermark_image") }}</p>
				</div>

				<!-- Preview canvas -->
				<div v-else class="relative w-full overflow-hidden rounded" style="min-height: 400px; background: #1e1e1e">
					<!-- Background photo or placeholder -->
					<img
						v-if="previewPhotoUrl"
						:src="previewPhotoUrl"
						class="w-full h-full object-contain"
						style="max-height: 600px; display: block"
						alt="Preview background"
					/>
					<div v-else class="flex flex-col items-center justify-center text-muted gap-2" style="min-height: 400px">
						<UIcon name="lucide:image" class="text-5xl opacity-30" />
						<p class="text-sm opacity-50">{{ $t("watermark.preview.no_preview_photo") }}</p>
					</div>

					<!-- Watermark overlay -->
					<img :src="watermarkImageUrl" class="absolute pointer-events-none" :style="watermarkStyle" alt="Watermark" />
				</div>
			</Fieldset>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from "vue";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useAppToast } from "@/v8/composables/useAppToast";
import SettingsService from "@/services/settings-service";
import ModerationService from "@/services/moderation-service";

type WatermarkPosition = "top-left" | "top" | "top-right" | "left" | "center" | "right" | "bottom-left" | "bottom" | "bottom-right";

const lycheeStore = useLycheeStateStore();
const toast = useAppToast();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);

const isSeAvailable = computed(() => is_se_enabled.value || is_se_preview_enabled.value);

// Settings state
const watermarkPhotoId = ref("");
const watermarkSize = ref(50);
const watermarkOpacity = ref(75);
const watermarkPosition = ref<WatermarkPosition>("center");

// Preview state
const watermarkImageUrl = ref<string | null>(null);
const watermarkLoadError = ref(false);
const previewPhotoId = ref("");
const previewPhotoUrl = ref<string | null>(null);
const previewLoadError = ref(false);
const saving = ref(false);

const positionOptions: { value: WatermarkPosition; label: string }[] = [
	{ value: "top-left", label: trans("watermark.preview.position_options.top-left") },
	{ value: "top", label: trans("watermark.preview.position_options.top") },
	{ value: "top-right", label: trans("watermark.preview.position_options.top-right") },
	{ value: "left", label: trans("watermark.preview.position_options.left") },
	{ value: "center", label: trans("watermark.preview.position_options.center") },
	{ value: "right", label: trans("watermark.preview.position_options.right") },
	{ value: "bottom-left", label: trans("watermark.preview.position_options.bottom-left") },
	{ value: "bottom", label: trans("watermark.preview.position_options.bottom") },
	{ value: "bottom-right", label: trans("watermark.preview.position_options.bottom-right") },
];

const watermarkStyle = computed(() => {
	const opacity = watermarkOpacity.value / 100;
	const size = watermarkSize.value;
	const pos = watermarkPosition.value;

	const base = `opacity: ${opacity}; width: ${size}%; max-width: ${size}%; object-fit: contain; position: absolute;`;

	const positionMap: Record<WatermarkPosition, string> = {
		"top-left": "top: 0; left: 0;",
		top: "top: 0; left: 50%; transform: translateX(-50%);",
		"top-right": "top: 0; right: 0;",
		left: "top: 50%; left: 0; transform: translateY(-50%);",
		center: "top: 50%; left: 50%; transform: translate(-50%, -50%);",
		right: "top: 50%; right: 0; transform: translateY(-50%);",
		"bottom-left": "bottom: 0; left: 0;",
		bottom: "bottom: 0; left: 50%; transform: translateX(-50%);",
		"bottom-right": "bottom: 0; right: 0;",
	};

	return base + " " + positionMap[pos];
});

function loadWatermarkImage() {
	const id = watermarkPhotoId.value.trim();
	if (!id) {
		watermarkImageUrl.value = null;
		watermarkLoadError.value = false;
		return;
	}

	watermarkLoadError.value = false;
	ModerationService.getPhoto(id)
		.then((resp) => {
			const photo = resp.data;
			const url =
				photo.size_variants.medium?.url ??
				photo.size_variants.small?.url ??
				photo.size_variants.thumb2x?.url ??
				photo.size_variants.thumb?.url ??
				photo.size_variants.original?.url ??
				null;
			watermarkImageUrl.value = url;
			if (!url) {
				watermarkLoadError.value = true;
			}
		})
		.catch(() => {
			watermarkImageUrl.value = null;
			watermarkLoadError.value = true;
		});
}

function loadPreviewPhoto() {
	const id = previewPhotoId.value.trim();
	if (!id) {
		previewPhotoUrl.value = null;
		previewLoadError.value = false;
		return;
	}

	previewLoadError.value = false;
	ModerationService.getPhoto(id)
		.then((resp) => {
			const photo = resp.data;
			const url =
				photo.size_variants.medium?.url ??
				photo.size_variants.small?.url ??
				photo.size_variants.thumb2x?.url ??
				photo.size_variants.thumb?.url ??
				photo.size_variants.original?.url ??
				null;
			previewPhotoUrl.value = url;
			if (!url) {
				previewLoadError.value = true;
			}
		})
		.catch(() => {
			previewPhotoUrl.value = null;
			previewLoadError.value = true;
		});
}

function save() {
	saving.value = true;
	SettingsService.setConfigs({
		configs: [
			{ key: "watermark_photo_id", value: watermarkPhotoId.value.trim() },
			{ key: "watermark_size", value: String(watermarkSize.value) },
			{ key: "watermark_opacity", value: String(watermarkOpacity.value) },
			{ key: "watermark_position", value: watermarkPosition.value },
		],
	})
		.then(() => {
			toast.add({ severity: "success", summary: trans("watermark.preview.saved"), life: 3000 });
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("watermark.preview.save_error"), detail: e?.response?.data?.message, life: 5000 });
		})
		.finally(() => {
			saving.value = false;
		});
}

function loadSettings() {
	SettingsService.getAll().then((resp) => {
		const allCategories = resp.data as App.Http.Resources.Models.ConfigCategoryResource[];
		const watermarkerCat = allCategories.find((c) => c.cat === "Mod Watermarker");
		if (!watermarkerCat) {
			return;
		}
		watermarkerCat.configs.forEach((config) => {
			if (config.key === "watermark_photo_id") {
				watermarkPhotoId.value = config.value ?? "";
				if (watermarkPhotoId.value) {
					loadWatermarkImage();
				}
			} else if (config.key === "watermark_size") {
				watermarkSize.value = parseInt(config.value ?? "50", 10) || 50;
			} else if (config.key === "watermark_opacity") {
				watermarkOpacity.value = parseInt(config.value ?? "75", 10) || 75;
			} else if (config.key === "watermark_position") {
				watermarkPosition.value = (config.value as WatermarkPosition) ?? "center";
			}
		});
	});
}

onMounted(() => {
	if (isSeAvailable.value) {
		loadSettings();
	}
});
</script>
