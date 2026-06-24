<template>
	<div v-if="isVisible && filteredDetections.length > 0" class="absolute inset-0 pointer-events-none">
		<!-- NSFW detection bounding box overlays -->
		<template v-for="detection in filteredDetections" :key="detection.id">
			<div
				class="absolute rounded transition-opacity duration-200 border-2"
				:class="tierClass(detection)"
				:style="{
					left: (detection.bbox_x / imageWidth) * 100 + '%',
					top: (detection.bbox_y / imageHeight) * 100 + '%',
					width: (detection.bbox_width / imageWidth) * 100 + '%',
					height: (detection.bbox_height / imageHeight) * 100 + '%',
				}"
			>
				<div
					class="absolute top-full left-0 mt-0.5 px-1.5 py-0.5 text-xs rounded whitespace-nowrap max-w-40 truncate text-white"
					:class="tierBadgeClass(detection)"
				>
					{{ formatLabel(detection.label) }} {{ (detection.confidence * 100).toFixed(0) }}%
				</div>
			</div>
		</template>

		<!-- Filter mode indicator -->
		<div class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded pointer-events-none">
			{{ modeLabel }}
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useLycheeStateStore } from "@/stores/LycheeState";

const props = defineProps<{
	detections: App.Http.Resources.Models.NsfwDetectionResource[];
	imageWidth: number;
	imageHeight: number;
}>();

const lycheeStore = useLycheeStateStore();

const modes = ["hidden", "all", "block", "review", "sensitive"] as const;

const isVisible = computed(() => lycheeStore.nsfw_overlay_mode !== "hidden");

const filteredDetections = computed(() => {
	const mode = lycheeStore.nsfw_overlay_mode;
	if (mode === "hidden") return [];
	if (mode === "all") return props.detections;
	if (mode === "block") return props.detections.filter((d) => d.is_block);
	if (mode === "review") return props.detections.filter((d) => d.is_review);
	return props.detections.filter((d) => d.is_sensitive);
});

const modeLabel = computed(() => {
	const mode = lycheeStore.nsfw_overlay_mode;
	if (mode === "all") return "NSFW: All";
	if (mode === "block") return "NSFW: Block";
	if (mode === "review") return "NSFW: Review";
	return "NSFW: Sensitive";
});

onKeyStroke("h", () => {
	if (shouldIgnoreKeystroke()) {
		return;
	}
	const idx = modes.indexOf(lycheeStore.nsfw_overlay_mode as (typeof modes)[number]);
	lycheeStore.nsfw_overlay_mode = modes[(idx + 1) % modes.length];
});

function tierClass(detection: App.Http.Resources.Models.NsfwDetectionResource): string[] {
	if (detection.is_block) return ["border-red-500"];
	if (detection.is_review) return ["border-orange-400"];
	return ["border-yellow-400"];
}

function tierBadgeClass(detection: App.Http.Resources.Models.NsfwDetectionResource): string {
	if (detection.is_block) return "bg-red-600";
	if (detection.is_review) return "bg-orange-500";
	return "bg-yellow-600";
}

function formatLabel(label: string): string {
	return label.replace(/_/g, " ").toLowerCase().replace(/^\w/, (c) => c.toUpperCase());
}
</script>
