<template>
	<div v-if="visible" class="absolute inset-0" :class="wrapperClass">
		<!-- ul/li "slide" scaffold: today there's always exactly one slide (this orientation pair),
		     but keeping it list-shaped means a future rotating background (multiple slides cycling)
		     can extend this component without another cross-cutting refactor of the four callers. -->
		<ul class="list-none">
			<li class="w-full h-full">
				<img
					v-if="landscape !== ''"
					:src="landscape"
					:alt="alt"
					class="w-full h-full object-cover absolute top-0 left-0"
					:class="[landscapeImageClass, imageClass]"
				/>
				<img
					v-if="portrait !== ''"
					:src="portrait"
					:alt="alt"
					class="w-full h-full object-cover absolute top-0 left-0"
					:class="[portraitImageClass, imageClass]"
				/>
			</li>
		</ul>
	</div>
</template>
<script setup lang="ts">
import { toRef } from "vue";
import { useLandingBackgroundOrientation, type LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";

type ClassBinding = string | Record<string, boolean> | ClassBinding[];

const props = withDefaults(
	defineProps<{
		landscape: string;
		portrait: string;
		previewOrientation?: LandingPreviewOrientation;
		/** Applied to the wrapper, not each image - so per-caller entrance/timing animations only
		 *  need to run once, and any future per-slide animation has a single place to hook into. */
		wrapperClass?: ClassBinding;
		/** Applied to both images uniformly (e.g. Studio's static `opacity-50` dimming). */
		imageClass?: ClassBinding;
		visible?: boolean;
		alt?: string;
	}>(),
	{
		previewOrientation: undefined,
		wrapperClass: "",
		imageClass: "",
		visible: true,
		alt: "",
	},
);

const { landscapeImageClass, portraitImageClass } = useLandingBackgroundOrientation(toRef(props, "previewOrientation"));
</script>
