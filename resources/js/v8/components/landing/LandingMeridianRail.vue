<template>
	<RouterLink
		:to="to"
		class="fixed top-0 w-20 h-full z-30 -translate-x-1/2 cursor-pointer group before:content-[''] before:absolute before:inset-y-0 before:left-1/2 before:-translate-x-1/2 before:w-px before:bg-white/25 hover:before:bg-white/60 before:transition-colors opacity-50 hover:opacity-100 transition-all duration-300"
		:style="{ left: `${props.linePosition}%` }"
		:class="routerClass"
	>
		<div
			class="absolute left-1/2 flex flex-col items-center shrink-0"
			:style="{
				top: `${props.labelOffset}%`,
				transform: 'translate(-50%, -50%) rotate(-90deg)',
			}"
		>
			<span
				class="text-2xs/28 uppercase font-bold tracking-[0.3em] h-16 text-white/70 group-hover:text-white duration-500 transition-all ease-in-out"
				:class="captionEntranceClassComputed"
			>
				{{ props.caption }}
			</span>
			<span
				class="text-3xl uppercase tracking-widest font-bold h-16 text-white transition-all duration-300"
				:class="labelEntranceClassComputed"
			>
				{{ props.label }}
			</span>
		</div>
	</RouterLink>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { RouterLink, type RouteLocationRaw } from "vue-router";

const props = defineProps<{
	to: RouteLocationRaw;
	/** Which end of the rail the line grows in from (`before:origin-*`, matches the growth keyframe's transform-origin). */
	origin: "top" | "bottom";
	/** Horizontal position of the rail, as a percentage from the left. */
	linePosition: number;
	/** Vertical position of the caption/label along the rail, as a percentage from the top. */
	labelOffset: number;
	caption: string;
	label: string;
	/** Passed through from the parent's `useLandingAnimation()` / meridian reveal-sequence computeds - identical for every rail, so computed once there rather than per-rail here. */
	lineAnimationClass: string;
	introDelayClass: string;
	captionEntranceClass: string[];
	labelEntranceClass: string[];
}>();

const routerClass = computed(() => [
	props.origin === "top" ? "before:origin-top" : "before:origin-bottom",
	props.lineAnimationClass,
	props.introDelayClass,
]);
const labelEntranceClassComputed = computed(() => {
	// Decapsulation to avoid contamination via proxy
	const labelEntrance = [];
	if (props.origin === "top") {
		labelEntrance.push(...props.labelEntranceClass);
		labelEntrance.push("translate-x-8 group-hover:translate-x-6");
	} else {
		labelEntrance.push(...props.captionEntranceClass);
		labelEntrance.push("-translate-x-8 group-hover:-translate-x-6");
	}
	return labelEntrance;
});
const captionEntranceClassComputed = computed(() => {
	// Decapsulation to avoid contamination via proxy
	const captionEntrance = [];
	if (props.origin === "top") {
		captionEntrance.push(...props.captionEntranceClass);
		captionEntrance.push("-translate-x-8 group-hover:-translate-x-6");
	} else {
		captionEntrance.push(...props.labelEntranceClass);
		captionEntrance.push("translate-x-8 group-hover:translate-x-6");
	}
	return captionEntrance;
});
</script>
<style lang="css" scoped>
/* `::before` needs its own selector: the plain rule below doesn't reach a pseudo-element. */
.no-intro-delay,
.no-intro-delay::before {
	animation-delay: 0s !important;
}

/* Without the splash screen's 4s cover, the line-grow's normal 0.2s duration (set for a
   handoff hidden behind that cover, see app-v8.css) plays out in full view and reads as an
   abrupt snap. Stretch it out here so it's still a deliberate motion when watched from frame 0. */
.no-intro-delay::before {
	animation-duration: 0.6s !important;
}

/* Mirrors LandingMeridian.vue's own copy of this rule (needed there for the background/header
   elements that stay in the parent) - see that file's comment for why this offset exists at all. */
.meridian-after-lines {
	animation-delay: 0.13s !important;
}
.meridian-after-lines-caption {
	animation-delay: 0.28s !important;
}
</style>
