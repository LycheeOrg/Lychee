<template>
	<div v-if="isVisible && filteredDetections.length > 0" class="absolute inset-0 pointer-events-none">
		<!-- NSFW detection bounding box overlays -->
		<template v-for="detection in filteredDetections" :key="detection.id">
			<div
				class="group absolute rounded transition-opacity duration-200 border-2 pointer-events-auto hover:z-10"
				:class="tierClass(detection)"
				:style="{
					left: (detection.bbox_x / imageWidth) * 100 + '%',
					top: (detection.bbox_y / imageHeight) * 100 + '%',
					width: (detection.bbox_width / imageWidth) * 100 + '%',
					height: (detection.bbox_height / imageHeight) * 100 + '%',
				}"
			>
				<div
					class="absolute top-full left-0 mt-0.5 px-1.5 py-0.5 text-xs rounded text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200"
					:class="tierBadgeClass(detection)"
				>
					<div class="font-semibold whitespace-nowrap">{{ formatLabel(detection.label) }}</div>
					<div class="whitespace-nowrap">{{ trans("moderation.overlay_confidence", { value: (detection.confidence * 100).toFixed(0) }) }} · {{ trans("moderation.overlay_area", { value: areaLabel(detection) }) }}</div>
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
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";

const props = defineProps<{
	detections: App.Http.Resources.Models.NsfwDetectionResource[];
	imageWidth: number;
	imageHeight: number;
}>();

const lycheeStore = useLycheeStateStore();

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
	if (mode === "all") return trans("moderation.overlay_mode_all");
	if (mode === "block") return trans("moderation.overlay_mode_block");
	if (mode === "review") return trans("moderation.overlay_mode_review");
	return trans("moderation.overlay_mode_sensitive");
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
	return label
		.replace(/_/g, " ")
		.toLowerCase()
		.replace(/^\w/, (c) => c.toUpperCase());
}

function areaLabel(detection: App.Http.Resources.Models.NsfwDetectionResource): string {
	const ratio = ((detection.bbox_width * detection.bbox_height) / (props.imageWidth * props.imageHeight)) * 100;
	return `${ratio.toFixed(1)}%`;
}
</script>
