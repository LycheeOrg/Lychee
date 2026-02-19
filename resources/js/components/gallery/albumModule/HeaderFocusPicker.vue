<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";
import { FocusPicker } from "image-focus";

const props = defineProps<{
	src: string;
	focusX: number | null;
	focusY: number | null;
}>();

const emit = defineEmits<{
	"update:focus": [x: number, y: number];
	close: [];
	cancel: [];
}>();

const imageRef = ref<HTMLImageElement | null>(null);
const pickerInstance = ref<FocusPicker | null>(null);

function initializePicker() {
	if (!imageRef.value) return;

	// If already initialized, do nothing or destroy/re-create?
	// image-focus doesn't seem to have a destroy method in types, but we should check.
	// For now, avoid re-initializing if instance exists.
	if (pickerInstance.value) return;

	const initialFocus = {
		x: props.focusX ?? 0,
		y: props.focusY ?? 0,
	};

	try {
		pickerInstance.value = new FocusPicker(imageRef.value, {
			focus: initialFocus,
			onChange: (focus) => {
				emit("update:focus", focus.x, focus.y);
			},
		});
	} catch (e) {
		console.warn("Failed to initialize FocusPicker", e);
	}
}

onMounted(() => {
	if (imageRef.value?.complete) {
		initializePicker();
	} else {
		imageRef.value?.addEventListener("load", initializePicker);
	}
});

onUnmounted(() => {
	if (imageRef.value) {
		imageRef.value.removeEventListener("load", initializePicker);
	}
	// Attempt to clean up if supported, otherwise just clear ref
	pickerInstance.value = null;
});

watch(
	() => props.src,
	() => {
		// Re-init if src changes?
	},
);
</script>

<template>
	<div class="absolute top-10 right-1 z-50 w-72 bg-surface-800 rounded-lg shadow-xl overflow-hidden border border-surface-700 z-100">
		<div class="p-3 border-b border-surface-700 flex justify-between items-center">
			<h3 class="text-sm font-semibold text-surface-0">{{ $t("gallery.set_header_focus") }}</h3>
			<button @click="$emit('cancel')" class="text-surface-400 hover:text-white transition-colors cursor-pointer">
				<i class="pi pi-times text-sm"></i>
			</button>
		</div>

		<div class="p-3 bg-black/50 flex justify-center items-center">
			<div class="relative w-full aspect-video">
				<img ref="imageRef" :src="props.src" class="w-full h-full object-contain" alt="Header Image Focus Picker" />
			</div>
		</div>

		<div class="p-3 border-t border-surface-700 flex justify-end gap-2">
			<button
				@click="$emit('cancel')"
				class="px-3 py-1 rounded bg-surface-600 hover:bg-surface-500 text-white text-sm font-medium transition-colors cursor-pointer"
			>
				{{ $t("gallery.cancel") }}
			</button>
			<button
				@click="$emit('close')"
				class="px-3 py-1 rounded bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium transition-colors cursor-pointer"
			>
				{{ $t("gallery.done") }}
			</button>
		</div>
	</div>
</template>

<style scoped>
/* Ensure the picker handle is visible */
:deep(.focus-picker-handle) {
	border: 2px solid white;
	box-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
}
</style>
