<template>
	<div id="landing" class="fixed inset-0 bg-black overflow-hidden">
		<div id="header" class="fixed top-0 left-0 right-0 z-50 overflow-y-hidden">
			<div id="logo" class="float-left p-4 text-white" :class="[entranceDownClass, introDelayClass]">
				<a href="#">
					<img
						v-if="data.landing_header_logo !== ''"
						id="landing-header-logo"
						:src="data.landing_header_logo"
						alt="logo"
						class="h-10 object-contain"
					/>
					<template v-else>
						<h1 class="text-lg font-bold uppercase text-center">
							{{ data.landing_title }}
							<span class="p-0 text-2xs block font-thin tracking-wide leading-0">{{ data.landing_subtitle }}</span>
						</h1>
					</template>
				</a>
			</div>
		</div>

		<div id="menu_wrap" class="fixed top-0 right-0 z-50 w-4/5 overflow-y-hidden">
			<div id="menu" class="w-full" :class="[entranceDownClass, introDelayClass]">
				<ul class="menu list-none">
					<li v-for="link in navLinks" :key="link.id" class="menu-item relative block float-right pt-6 pb-5 px-3">
						<RouterLink
							v-if="link.is_built_in"
							:to="{ name: link.url }"
							class="cursor-pointer block text-xs uppercase font-normal text-white hover:text-muted"
						>
							{{ link.label }}
						</RouterLink>
						<a
							v-else
							:href="link.url"
							:target="link.open_in_new_tab ? '_blank' : undefined"
							:rel="link.open_in_new_tab ? 'noopener' : undefined"
							class="cursor-pointer block text-xs uppercase font-normal text-white hover:text-muted"
						>
							{{ link.label }}
						</a>
					</li>
				</ul>
			</div>
		</div>

		<LandingIntroScreen :data="data" :effective-preset="effectivePreset" />

		<div id="slides" class="bg-black absolute overflow-hidden top-0 left-0 right-0 bottom-[2%]">
			<LandingBackgroundImages
				:landscape="data.landing_background_landscape"
				:portrait="data.landing_background_portrait"
				:preview-orientation="previewOrientation"
				:wrapper-class="effectivePreset !== 'none' ? ['opacity-0', 'animate-landingSlidesPopIn', introDelayClass] : ''"
				alt="landing image"
			/>
			<LandingBackdrop :opacity="data.backdrop_opacity" />
			<div :style="ctaStyle">
				<span
					class="pointer-events-none absolute inset-0 flex items-center justify-center text-transparent uppercase text-3xl filter-shadow-darker py-10 px-40"
					aria-hidden="true"
				>
					{{ ctaText }}
				</span>
				<RouterLink
					:to="{ name: 'home' }"
					class="cursor-pointer block text-2xl uppercase text-white hover:scale-125 transition-all duration-300 p-10 filter-shadow text-center"
					:class="effectivePreset !== 'none' ? ['animate-landingEnterPopIn', 'opacity-0', introDelayClass] : ''"
				>
					{{ ctaText }}<br class="md:hidden" />
					<template v-if="isLTR()">
						<UIcon name="lucide:chevron-right" size="3rem" class="inline-block animate-pulseTo0 animate-infinite" />
						<UIcon
							name="lucide:chevron-right"
							size="3rem"
							class="inline-block animate-pulseTo0 animate-delay-500 animate-infinite -ml-8"
						/>
						<UIcon
							name="lucide:chevron-right"
							size="3rem"
							class="inline-block animate-pulseTo0 animate-delay-1000 animate-infinite -ml-8"
						/>
					</template>
					<template v-else>
						<UIcon name="lucide:chevron-left" size="3rem" class="inline-block animate-pulseTo0 animate-infinite" />
						<UIcon
							name="lucide:chevron-left"
							size="3rem"
							class="inline-block animate-pulseTo0 animate-delay-500 animate-infinite -mr-8"
						/>
						<UIcon
							name="lucide:chevron-left"
							size="3rem"
							class="inline-block animate-pulseTo0 animate-delay-1000 animate-infinite -mr-8"
						/>
					</template>
				</RouterLink>
			</div>
		</div>
		<LandingFooter
			:footer-data="data.footer"
			:links="data.links"
			:animated="effectivePreset !== 'none'"
			:no-intro-delay="!data.intro_screen_enabled"
		/>
	</div>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink } from "vue-router";
import LandingFooter from "@/v8/components/footers/LandingFooter.vue";
import LandingIntroScreen from "@/v8/components/landing/LandingIntroScreen.vue";
import LandingBackgroundImages from "@/v8/components/landing/LandingBackgroundImages.vue";
import LandingBackdrop from "@/v8/components/landing/LandingBackdrop.vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { useLandingCtaPosition } from "@/v8/composables/landing/useLandingCtaPosition";
import { useLandingAnimation } from "@/v8/composables/landing/useLandingAnimation";
import type { LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";
import { trans } from "laravel-vue-i18n";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	previewOrientation?: LandingPreviewOrientation;
}>();

const landingData = toRef(props, "data");

const { ctaStyle } = useLandingCtaPosition(landingData);
const { effectivePreset, introDelayClass } = useLandingAnimation(landingData);

const entranceDownClass = computed(() => (effectivePreset.value !== "none" ? "animate-landingAnimateDown" : ""));

const ctaText = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.access_gallery")));

const navLinks = computed(() => props.data.links.filter((link) => link.placement === "nav" || link.placement === "both"));
</script>
<style lang="css" scoped>
.animate-landingAnimateDown {
	opacity: 0;
	translate: translateY(-300px);
	animation-name: landingAnimateDown;
	animation-duration: 2s;
	animation-timing-function: ease-in-out;
	animation-delay: 3s;
	animation-direction: forwards;
	animation-fill-mode: forwards;
}

@keyframes landingAnimateDown {
	0% {
		transform: translateY(-300px);
		opacity: 0;
	}
	100% {
		transform: translateY(0);
		opacity: 1;
	}
}

.no-intro-delay {
	animation-delay: 0s !important;
}

.light-delay {
	animation-delay: 0.5s !important;
}
</style>
