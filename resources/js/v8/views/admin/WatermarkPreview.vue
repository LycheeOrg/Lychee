<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("watermark.preview.title") }}
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
						<label class="text-sm font-medium">{{ $t("watermark.preview.size", { value: String(watermarkSize) }) }}</label>
						<input
							type="range"
							min="1"
							max="100"
							class="w-full accent-primary-500"
							:value="String(watermarkSize)"
							@input="watermarkSize = updateNumberInput($event.target)"
						/>
					</div>

					<!-- Opacity slider -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.opacity", { value: String(watermarkOpacity) }) }}</label>
						<input
							type="range"
							min="1"
							max="100"
							class="w-full accent-primary-500"
							:value="String(watermarkOpacity)"
							@input="watermarkOpacity = updateNumberInput($event.target)"
						/>
					</div>

					<!-- Position grid -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.position") }}</label>
						<div class="grid grid-cols-3 gap-1" dir="ltr">
							<UButton
								v-for="pos in positionOptions"
								:key="pos.value"
								:label="pos.label"
								size="xs"
								:color="watermarkPosition === pos.value ? 'primary' : 'neutral'"
								:variant="watermarkPosition === pos.value ? 'solid' : 'ghost'"
								class="text-xs"
								@click="setWatermarkPosition(pos.value)"
							/>
						</div>
					</div>
				</div>
			</Fieldset>

			<!-- Shift / offset -->
			<Fieldset :legend="$t('watermark.preview.section_shift')">
				<div class="flex flex-col gap-4">
					<!-- Shift unit -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.shift_type") }}</label>
						<div class="grid grid-cols-2 gap-1">
							<UButton
								:label="$t('watermark.preview.shift_type_options.relative')"
								size="xs"
								:color="watermarkShiftType === 'relative' ? 'primary' : 'neutral'"
								:variant="watermarkShiftType === 'relative' ? 'solid' : 'ghost'"
								class="text-xs"
								@click="setWatermarkShiftType('relative')"
							/>
							<UButton
								:label="$t('watermark.preview.shift_type_options.absolute')"
								size="xs"
								:color="watermarkShiftType === 'absolute' ? 'primary' : 'neutral'"
								:variant="watermarkShiftType === 'absolute' ? 'solid' : 'ghost'"
								class="text-xs"
								@click="setWatermarkShiftType('absolute')"
							/>
						</div>
						<small class="text-muted text-xs">{{ $t("watermark.preview.shift_type_hint") }}</small>
					</div>

					<!-- Input mode toggle -->
					<div class="flex justify-end">
						<UButton
							:label="
								shiftInputMode === 'slider'
									? $t('watermark.preview.shift_mode_use_classic')
									: $t('watermark.preview.shift_mode_use_slider')
							"
							:icon="shiftInputMode === 'slider' ? 'lucide:list' : 'lucide:sliders-horizontal'"
							size="xs"
							color="neutral"
							variant="ghost"
							class="text-xs"
							@click="toggleShiftInputMode"
						/>
					</div>

					<!-- Horizontal shift (locked to LTR: "left"/"right" refer to the physical photo, not text direction) -->
					<div class="flex flex-col gap-1">
						<label class="text-sm font-medium">{{ $t("watermark.preview.shift_x", { value: String(sliderX) }) }}</label>

						<div v-if="shiftInputMode === 'slider'" class="flex items-center gap-2" dir="ltr">
							<span class="text-xs text-muted w-10 shrink-0 text-right">{{
								$t("watermark.preview.shift_x_direction_options.left")
							}}</span>
							<div class="relative flex-1">
								<div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 h-full w-px bg-muted/50" />
								<input
									type="range"
									min="-100"
									max="100"
									step="1"
									class="w-full accent-primary-500"
									:value="String(sliderX)"
									@input="sliderX = updateNumberInput($event.target)"
								/>
							</div>
							<span class="text-xs text-muted w-10 shrink-0">{{ $t("watermark.preview.shift_x_direction_options.right") }}</span>
						</div>

						<div v-else class="flex gap-2 items-center">
							<UInputNumber v-model="watermarkShiftX" :min="0" class="w-24" />
							<div class="grid grid-cols-2 gap-1 flex-1">
								<UButton
									:label="$t('watermark.preview.shift_x_direction_options.left')"
									size="xs"
									:color="watermarkShiftXDirection === 'left' ? 'primary' : 'neutral'"
									:variant="watermarkShiftXDirection === 'left' ? 'solid' : 'ghost'"
									class="text-xs"
									@click="setWatermarkShiftXDirection('left')"
								/>
								<UButton
									:label="$t('watermark.preview.shift_x_direction_options.right')"
									size="xs"
									:color="watermarkShiftXDirection === 'right' ? 'primary' : 'neutral'"
									:variant="watermarkShiftXDirection === 'right' ? 'solid' : 'ghost'"
									class="text-xs"
									@click="setWatermarkShiftXDirection('right')"
								/>
							</div>
						</div>
					</div>

					<!-- Vertical shift (locked to LTR: layout only, "up"/"down" text is unaffected by direction) -->
					<div class="flex flex-col gap-1" dir="ltr">
						<label class="text-sm font-medium">{{ $t("watermark.preview.shift_y", { value: String(sliderY) }) }}</label>

						<div v-if="shiftInputMode === 'slider'" class="flex items-center gap-2" dir="ltr">
							<span class="text-xs text-muted w-10 shrink-0 text-right">{{
								$t("watermark.preview.shift_y_direction_options.down")
							}}</span>
							<div class="relative flex-1">
								<div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 h-full w-px bg-muted/50" />
								<input
									type="range"
									min="-100"
									max="100"
									step="1"
									class="w-full accent-primary-500"
									:value="String(sliderY)"
									@input="sliderY = updateNumberInput($event.target)"
								/>
							</div>
							<span class="text-xs text-muted w-10 shrink-0">{{ $t("watermark.preview.shift_y_direction_options.up") }}</span>
						</div>

						<div v-else class="flex gap-2 items-center">
							<UInputNumber v-model="watermarkShiftY" :min="0" class="w-24" />
							<div class="grid grid-cols-2 gap-1 flex-1">
								<UButton
									:label="$t('watermark.preview.shift_y_direction_options.up')"
									size="xs"
									:color="watermarkShiftYDirection === 'up' ? 'primary' : 'neutral'"
									:variant="watermarkShiftYDirection === 'up' ? 'solid' : 'ghost'"
									class="text-xs"
									@click="setWatermarkShiftYDirection('up')"
								/>
								<UButton
									:label="$t('watermark.preview.shift_y_direction_options.down')"
									size="xs"
									:color="watermarkShiftYDirection === 'down' ? 'primary' : 'neutral'"
									:variant="watermarkShiftYDirection === 'down' ? 'solid' : 'ghost'"
									class="text-xs"
									@click="setWatermarkShiftYDirection('down')"
								/>
							</div>
						</div>
					</div>
				</div>
			</Fieldset>
		</div>

		<!-- ─────────────── RIGHT: Live Preview ─────────────── -->
		<div class="flex-1 flex flex-col gap-4">
			<Fieldset :legend="$t('watermark.preview.section_preview')" class="h-full">
				<div class="flex flex-col gap-4">
					<!-- Preview background photo -->
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

					<p class="text-muted text-xs text-center">{{ $t("watermark.preview.disclaimer") }}</p>
				</div>
			</Fieldset>

			<!-- Save -->
			<div class="flex justify-end">
				<UButton :label="$t('watermark.preview.save')" :loading="saving" icon="lucide:save" color="primary" @click="save" />
			</div>
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

const lycheeStore = useLycheeStateStore();
const toast = useAppToast();
const { is_se_enabled, is_se_preview_enabled } = storeToRefs(lycheeStore);

const isSeAvailable = computed(() => is_se_enabled.value || is_se_preview_enabled.value);

// Settings state
const watermarkPhotoId = ref("");
const watermarkSize = ref(50);
const watermarkOpacity = ref(75);
const watermarkPosition = ref<App.Enum.WatermarkPosition>("center");
const watermarkShiftType = ref<App.Enum.ShiftType>("relative");
const watermarkShiftX = ref(0);
const watermarkShiftXDirection = ref<App.Enum.ShiftX>("right");
const watermarkShiftY = ref(0);
const watermarkShiftYDirection = ref<App.Enum.ShiftY>("up");
const shiftInputMode = ref<"slider" | "classic">("slider");

function setWatermarkPosition(pos: App.Enum.WatermarkPosition) {
	watermarkPosition.value = pos;
}
function setWatermarkShiftType(type: App.Enum.ShiftType) {
	watermarkShiftType.value = type;
}
function setWatermarkShiftXDirection(dir: App.Enum.ShiftX) {
	watermarkShiftXDirection.value = dir;
}
function setWatermarkShiftYDirection(dir: App.Enum.ShiftY) {
	watermarkShiftYDirection.value = dir;
}
function toggleShiftInputMode() {
	shiftInputMode.value = shiftInputMode.value === "slider" ? "classic" : "slider";
}
function updateNumberInput(target: EventTarget | null): number {
	return Number((target as HTMLInputElement).value);
}

// Single signed slider (-100..100) standing in for the magnitude + direction pair: negative
// values map to "left"/"down", positive values map to "right"/"up".
const sliderX = computed<number>({
	get: () => (watermarkShiftXDirection.value === "left" ? -watermarkShiftX.value : watermarkShiftX.value),
	set: (v) => {
		watermarkShiftXDirection.value = v < 0 ? "left" : "right";
		watermarkShiftX.value = Math.abs(v);
	},
});
const sliderY = computed<number>({
	get: () => (watermarkShiftYDirection.value === "down" ? -watermarkShiftY.value : watermarkShiftY.value),
	set: (v) => {
		watermarkShiftYDirection.value = v < 0 ? "down" : "up";
		watermarkShiftY.value = Math.abs(v);
	},
});

// Preview state
const watermarkImageUrl = ref<string | null>(null);
const watermarkLoadError = ref(false);
const previewPhotoId = ref("");
const previewPhotoUrl = ref<string | null>(null);
const previewLoadError = ref(false);
const saving = ref(false);

const positionOptions: { value: App.Enum.WatermarkPosition; label: string }[] = [
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

// Signed shift amount, expressed in the unit matching `watermarkShiftType` (% of the preview
// canvas for "relative", raw CSS px for "absolute" — mirrors ShiftX/ShiftY direction signs used
// by CoordinateCalculator::apply_shift() on the backend: right/down are positive.
const shiftXCss = computed(() => {
	const signed = (watermarkShiftXDirection.value === "left" ? -1 : 1) * watermarkShiftX.value;
	return watermarkShiftType.value === "relative" ? `${signed}%` : `${signed}px`;
});
const shiftYCss = computed(() => {
	const signed = (watermarkShiftYDirection.value === "up" ? -1 : 1) * watermarkShiftY.value;
	return watermarkShiftType.value === "relative" ? `${signed}%` : `${signed}px`;
});

const watermarkStyle = computed(() => {
	const opacity = watermarkOpacity.value / 100;
	const size = watermarkSize.value;
	const pos = watermarkPosition.value;
	const sx = shiftXCss.value;
	const sy = shiftYCss.value;

	const base = `opacity: ${opacity}; width: ${size}%; max-width: ${size}%; object-fit: contain; position: absolute;`;

	// left/right/top/bottom are additive with the configured shift; right/bottom are inverted
	// since increasing "right"/"bottom" moves the element towards the center, not away from it.
	// Each is wrapped in clamp(0%, ..., 100%), mirroring the backend's own bounds check
	// (CoordinateCalculator::apply_shift() clamps the shifted coordinate to [0, image_dimension])
	// so a large shift can't push the watermark's anchor edge past the photo in the preview
	// when it wouldn't be allowed to in the real output.
	const positionMap: Record<App.Enum.WatermarkPosition, string> = {
		"top-left": `top: clamp(0%, calc(0% + ${sy}), 100%); left: clamp(0%, calc(0% + ${sx}), 100%);`,
		top: `top: clamp(0%, calc(0% + ${sy}), 100%); left: clamp(0%, calc(50% + ${sx}), 100%); transform: translateX(-50%);`,
		"top-right": `top: clamp(0%, calc(0% + ${sy}), 100%); right: clamp(0%, calc(0% - ${sx}), 100%);`,
		left: `top: clamp(0%, calc(50% + ${sy}), 100%); left: clamp(0%, calc(0% + ${sx}), 100%); transform: translateY(-50%);`,
		center: `top: clamp(0%, calc(50% + ${sy}), 100%); left: clamp(0%, calc(50% + ${sx}), 100%); transform: translate(-50%, -50%);`,
		right: `top: clamp(0%, calc(50% + ${sy}), 100%); right: clamp(0%, calc(0% - ${sx}), 100%); transform: translateY(-50%);`,
		"bottom-left": `bottom: clamp(0%, calc(0% - ${sy}), 100%); left: clamp(0%, calc(0% + ${sx}), 100%);`,
		bottom: `bottom: clamp(0%, calc(0% - ${sy}), 100%); left: clamp(0%, calc(50% + ${sx}), 100%); transform: translateX(-50%);`,
		"bottom-right": `bottom: clamp(0%, calc(0% - ${sy}), 100%); right: clamp(0%, calc(0% - ${sx}), 100%);`,
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
			{ key: "watermark_shift_type", value: watermarkShiftType.value },
			{ key: "watermark_shift_x", value: String(watermarkShiftX.value) },
			{ key: "watermark_shift_x_direction", value: watermarkShiftXDirection.value },
			{ key: "watermark_shift_y", value: String(watermarkShiftY.value) },
			{ key: "watermark_shift_y_direction", value: watermarkShiftYDirection.value },
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
				watermarkPosition.value = (config.value as App.Enum.WatermarkPosition) ?? "center";
			} else if (config.key === "watermark_shift_type") {
				watermarkShiftType.value = (config.value as App.Enum.ShiftType) ?? "relative";
			} else if (config.key === "watermark_shift_x") {
				watermarkShiftX.value = parseInt(config.value ?? "0", 10) || 0;
			} else if (config.key === "watermark_shift_x_direction") {
				watermarkShiftXDirection.value = (config.value as App.Enum.ShiftX) ?? "right";
			} else if (config.key === "watermark_shift_y") {
				watermarkShiftY.value = parseInt(config.value ?? "0", 10) || 0;
			} else if (config.key === "watermark_shift_y_direction") {
				watermarkShiftYDirection.value = (config.value as App.Enum.ShiftY) ?? "up";
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
