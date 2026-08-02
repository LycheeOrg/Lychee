<template>
	<div>
		<div class="flex items-center gap-4 justify-between">
			<div class="w-1/2 sm:w-full" :class="props.config.require_se ? 'text-primary' : 'text-highlighted'">
				<UChip v-if="chip !== ''" standalone inset :color="chip" size="xl" /> {{ tDoc(props.config) }}
				<SETag v-if="config.require_se" />
			</div>
			<div class="flex gap-4 items-center">
				<ResetField v-if="changed" @click="reset" />
				<BlossomColorPicker
					:slider-position="sliderPosition"
					:open-on-hover="openOnHover"
					:value="val"
					:sliderOffset="0"
					@change="debouncedHandleChange"
				/>
			</div>
		</div>
		<div v-if="props.config.details" class="text-muted text-sm hidden sm:block" v-html="tDetails(props.config)" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ResetField from "@/v8/components/forms/settings/ResetField.vue";
import SETag from "@/v8/components/icons/SETag.vue";
import { useTranslation } from "@/composables/useTranslation";
import { BlossomColorPicker, BlossomColorPickerValue, BlossomColorPickerColor } from "@dayflow/blossom-color-picker-vue";
import { hexToHsl } from "@dayflow/blossom-color-picker";
import { useLtRorRtL } from "@/utils/Helpers";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useDebounceFn } from "@vueuse/core";

const { tDoc, tDetails } = useTranslation();

const { isLTR } = useLtRorRtL();

const sliderPosition = computed(() => (isLTR() ? "right" : "left"));
const openOnHover = computed(() => (isTouchDevice() ? false : false));

const COLORS = ["primary_color", "secondary_color", "warning_color", "error_color", "success_color", "info_color", "neutral_color"];

const chip = computed(() => {
	if (COLORS.includes(props.config.key)) {
		return props.config.key.replace("_color", "");
	}
	return "";
});

function getDefaultColor(key: string): string {
	switch (key) {
		case "primary_color":
			return "#00a6f4"; // sky
		case "secondary_color":
			return "#615fff"; // violet
		case "success_color":
			return "#00bc7d"; // emerald
		case "info_color":
			return "#00b8db"; // cyan
		case "warning_color":
			return "#fe9a00"; // amber
		case "error_color":
			return "#ff2056"; // rose
		case "neutral_color":
			const b = document.querySelector("body");
			if (b?.classList.contains("dark")) {
				return "#71717b"; // dark mode: zinc color
			}
			return "#62748e"; // or slate color
		default:
			return "#00a6f4"; // default to primary color
	}
}

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

function stringToBlossomColorPickerValue(config: App.Http.Resources.Models.ConfigResource): BlossomColorPickerValue | undefined {
	let color = config.value;
	if (!config.value) {
		color = getDefaultColor(config.key);
	}

	const hsl = hexToHsl(color);
	return {
		hue: hsl.h,
		saturation: 50,
		lightness: hsl.l,
		originalSaturation: hsl.s,
		alpha: 100,
		layer: "outer",
	};
}
const val = ref<BlossomColorPickerValue | undefined>(stringToBlossomColorPickerValue(props.config));

const changed = computed(() => {
	const originalValue = stringToBlossomColorPickerValue(props.config);

	return (
		val.value?.hue !== originalValue?.hue ||
		val.value?.saturation !== originalValue?.saturation ||
		val.value?.lightness !== originalValue?.lightness
	);
});

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

function reset() {
	emits("reset", props.config.key);
	val.value = stringToBlossomColorPickerValue(props.config);
}

const debouncedHandleChange = useDebounceFn((newColor: BlossomColorPickerColor) => {
	handleChange(newColor);
}, 300);

function handleChange(newColor: BlossomColorPickerColor) {
	val.value = newColor;
	emits("filled", props.config.key, `${newColor.hex}`);
}

// We watch props in case of updates.
watch(
	() => props.config,
	(newValue, _oldValue) => (val.value = stringToBlossomColorPickerValue(newValue)),
);
</script>
<style>
.bcp-bg-wrapper {
	display: none;
}
.bcp-bg-solid div {
	display: none;
}
.bcp-container svg:nth-child(2) {
	display: none;
}
</style>
