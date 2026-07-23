<template>
	<div>
		<div class="flex items-center gap-4 justify-between">
			<div class="w-1/2 sm:w-full" :class="props.config.require_se ? 'text-primary' : 'text-highlighted'">
				{{ tDoc(props.config) }}
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

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

function stringToBlossomColorPickerValue(color: string): BlossomColorPickerValue | undefined {
	if (!color) {
		color = "#00a6f4";
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
const val = ref<BlossomColorPickerValue | undefined>(stringToBlossomColorPickerValue(props.config.value));

const changed = computed(() => {
	const originalValue = stringToBlossomColorPickerValue(props.config.value);

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
	val.value = stringToBlossomColorPickerValue(props.config.value);
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
	(newValue, _oldValue) => (val.value = stringToBlossomColorPickerValue(newValue.value)),
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
