<template>
	<!-- Columns carry no z-index, so the clock box (z-30) stays above the shutter (z-20)
	     and the info box (z-15) sits under it, above the photo (z-10) (FR-088-11). -->
	<div
		v-for="corner in FRAME_CORNERS"
		:key="corner"
		class="pointer-events-none absolute flex flex-col gap-3 p-4"
		:class="CORNER_CLASSES[corner]"
		:data-frame-corner="corner"
	>
		<template v-for="kind in corners[corner]" :key="kind">
			<FrameClockBox v-if="kind === 'clock'" class="relative z-30" :clock="clock" />
			<FrameInfoBox v-else class="relative z-[15]" :info="info" :photo="photo" />
		</template>
	</div>
	<div
		v-if="hasClock && clock.position === 'center'"
		class="pointer-events-none absolute inset-0 flex items-center justify-center"
		data-frame-corner="center"
	>
		<FrameClockBox class="relative z-30 text-center" :clock="clock" />
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import FrameClockBox from "@/v8/components/frame/FrameClockBox.vue";
import FrameInfoBox from "@/v8/components/frame/FrameInfoBox.vue";
import { boxesByCorner, FRAME_CORNERS, type FrameClock, type FrameCorner, type FrameInfo, type FramePhoto } from "@/v8/utils/frameOverlay";

const props = defineProps<{
	clock: FrameClock;
	info: FrameInfo;
	photo: FramePhoto | null;
}>();

// Physical corners (FR-088-03). The Back button sits top left, top right in
// right-to-left languages: that column starts below it (FR-088-12).
const CORNER_CLASSES: Record<FrameCorner, string> = {
	top_left: "top-0 left-0 items-start text-left ltr:pt-16",
	top_right: "top-0 right-0 items-end text-right rtl:pt-16",
	bottom_left: "bottom-0 left-0 items-start text-left",
	bottom_right: "bottom-0 right-0 items-end text-right",
};

const hasClock = computed(() => props.clock.is_time_enabled || props.clock.is_date_enabled);
const corners = computed(() => boxesByCorner(hasClock.value ? props.clock.position : null, props.info.position));
</script>
