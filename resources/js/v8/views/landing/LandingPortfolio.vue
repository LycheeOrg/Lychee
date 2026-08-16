<template>
	<div class="w-full min-h-screen bg-black text-white">
		<!-- Sticky nav -->
		<nav class="sticky top-0 z-50 flex items-center justify-between px-6 py-4 bg-black/70 backdrop-blur">
			<a href="#" class="flex items-center">
				<img v-if="data.landing_header_logo !== ''" :src="data.landing_header_logo" alt="logo" class="h-8 object-contain" />
				<span v-else class="text-sm font-bold uppercase">{{ data.landing_title }}</span>
			</a>
			<ul class="flex items-center gap-6 list-none">
				<li v-for="link in navLinks" :key="link.id">
					<RouterLink v-if="link.is_built_in" :to="{ name: link.url }" class="text-xs uppercase hover:text-muted transition-colors">
						{{ link.label }}
					</RouterLink>
					<a
						v-else
						:href="link.url"
						:target="link.open_in_new_tab ? '_blank' : undefined"
						:rel="link.open_in_new_tab ? 'noopener' : undefined"
						class="text-xs uppercase hover:text-muted transition-colors"
					>
						{{ link.label }}
					</a>
				</li>
			</ul>
		</nav>

		<!-- Hero -->
		<section id="hero" class="relative h-[92vh] w-full overflow-hidden">
			<LandingBackgroundImages
				:landscape="data.landing_background_landscape"
				:portrait="data.landing_background_portrait"
				:preview-orientation="previewOrientation"
				:wrapper-class="heroEntranceClass"
			/>
			<LandingBackdrop :opacity="data.backdrop_opacity" />
			<div class="relative flex w-full h-full" :class="positionClasses">
				<div :class="heroEntranceClass">
					<h1 class="text-4xl md:text-6xl font-bold uppercase" :style="heroTextStyle">{{ data.landing_title }}</h1>
					<p class="mt-2 text-base md:text-lg" :style="heroTextStyle">{{ data.landing_subtitle }}</p>
				</div>
			</div>

			<div :style="ctaStyle">
				<RouterLink
					:to="{ name: 'home' }"
					class="inline-block px-8 py-3 bg-white text-black uppercase text-sm tracking-wide hover:scale-105 transition-transform"
					:class="heroEntranceClass"
				>
					{{ ctaText }}
				</RouterLink>
			</div>

			<button
				v-if="nextSectionId"
				type="button"
				class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/80 hover:text-white"
				:aria-label="$t('landing.portfolio.scroll_down')"
				@click="scrollToNextSection"
			>
				<UIcon name="lucide:chevron-down" size="2rem" :class="isReducedMotion ? '' : 'animate-bounce'" />
			</button>
		</section>

		<!-- About -->
		<section
			v-if="showAbout"
			id="about"
			ref="aboutEl"
			class="max-w-3xl mx-auto px-6 py-24 transition-all duration-700"
			:class="aboutVisible ? sectionRevealClass : sectionHiddenClass"
		>
			<h2 class="text-2xl font-bold uppercase mb-6">{{ $t("landing.portfolio.about") }}</h2>
			<div class="prose prose-invert max-w-none" v-html="data.about_text" />
		</section>

		<!-- Featured content -->
		<LandingPortfolioFeatured
			v-if="showFeatured"
			:items="data.featured_items"
			:is-scroll-driven="isScrollDriven"
			:section-reveal-class="sectionRevealClass"
		/>

		<LandingFooter
			:footer-data="data.footer"
			:links="data.links"
			:animated="effectivePreset !== 'none'"
			:no-intro-delay="!data.intro_screen_enabled"
			scrolling-socials
		/>

		<LandingIntroScreen :data="data" :effective-preset="effectivePreset" />
	</div>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink } from "vue-router";
import LandingFooter from "@/v8/components/footers/LandingFooter.vue";
import LandingIntroScreen from "@/v8/components/landing/LandingIntroScreen.vue";
import LandingBackgroundImages from "@/v8/components/landing/LandingBackgroundImages.vue";
import LandingBackdrop from "@/v8/components/landing/LandingBackdrop.vue";
import LandingPortfolioFeatured from "@/v8/components/landing/LandingPortfolioFeatured.vue";
import { useLandingTextPosition } from "@/v8/composables/landing/useLandingTextPosition";
import { useLandingAnimation } from "@/v8/composables/landing/useLandingAnimation";
import { useLandingCtaPosition } from "@/v8/composables/landing/useLandingCtaPosition";
import type { LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";
import { useScrollReveal } from "@/v8/composables/useScrollReveal";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	previewOrientation?: LandingPreviewOrientation;
}>();

const landingData = toRef(props, "data");

const { positionClasses } = useLandingTextPosition(landingData);
const { effectivePreset, isReducedMotion } = useLandingAnimation(landingData);
const { ctaStyle } = useLandingCtaPosition(landingData);

const navLinks = computed(() => props.data.links.filter((link) => link.placement === "nav" || link.placement === "both"));

const ctaText = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.access_gallery")));

const heroTextStyle = computed(() => ({
	color: props.data.hero_text_color !== "" ? props.data.hero_text_color : "#ffffff",
	opacity: props.data.hero_text_opacity / 100,
}));

const heroEntranceClass = computed(() => {
	switch (effectivePreset.value) {
		case "zoom_in":
			return "animate-landingZoomReveal";
		case "slide_reveal":
			return "animate-landingSlideReveal";
		case "none":
			return "";
		default:
			return "animate-landingIntroPopIn";
	}
});

const isScrollDriven = computed(() => effectivePreset.value === "parallax_scroll");

const sectionRevealClass = computed(() => {
	switch (effectivePreset.value) {
		case "zoom_in":
			return "animate-landingZoomReveal";
		case "slide_reveal":
		case "parallax_scroll":
			return "animate-landingSlideReveal";
		case "none":
			return "";
		default:
			return "animate-landingIntroPopIn";
	}
});
const sectionHiddenClass = computed(() => (isScrollDriven.value ? "opacity-0" : ""));

const showAbout = computed(() => props.data.about_enabled && props.data.about_text !== "");
const showFeatured = computed(() => props.data.featured_items_enabled && props.data.featured_items.length > 0);

const { el: aboutEl, isVisible: aboutVisible } = useScrollReveal(isScrollDriven);

const nextSectionId = computed(() => {
	if (showAbout.value) {
		return "about";
	}
	if (showFeatured.value) {
		return "featured";
	}
	return null;
});

function scrollToNextSection(): void {
	if (nextSectionId.value === null) {
		return;
	}
	document.getElementById(nextSectionId.value)?.scrollIntoView({ behavior: isReducedMotion.value ? "auto" : "smooth" });
}
</script>
