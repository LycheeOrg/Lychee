<template>
	<main id="landing" class="fixed inset-0 bg-black overflow-hidden">
		<img
			class="w-full h-full object-cover absolute top-0 left-0"
			:class="[landscapeImageClass, entranceClass]"
			:src="data.landing_background_landscape"
			alt="landing image"
		/>
		<img
			class="w-full h-full object-cover absolute top-0 left-0"
			:class="[portraitImageClass, entranceClass]"
			:src="data.landing_background_portrait"
			alt="landing image"
		/>
		<div class="absolute inset-0 bg-black/35" />

		<div id="header" class="fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-6 py-5" :class="entranceClass">
			<a href="#" class="flex items-center">
				<img v-if="data.landing_header_logo !== ''" :src="data.landing_header_logo" alt="logo" class="h-8 object-contain" />
				<span v-else class="text-sm font-bold uppercase tracking-widest text-white">{{ data.landing_title }}</span>
			</a>
			<ul class="flex items-center gap-6 list-none">
				<li v-for="link in navLinks" :key="link.id">
					<RouterLink
						v-if="link.is_built_in"
						:to="{ name: link.url }"
						class="text-xs uppercase tracking-widest text-white hover:text-muted transition-colors"
					>
						{{ link.label }}
					</RouterLink>
					<a
						v-else
						:href="link.url"
						:target="link.open_in_new_tab ? '_blank' : undefined"
						:rel="link.open_in_new_tab ? 'noopener' : undefined"
						class="text-xs uppercase tracking-widest text-white hover:text-muted transition-colors"
					>
						{{ link.label }}
					</a>
				</li>
			</ul>
		</div>

		<LandingIntroScreen :data="data" :effective-preset="effectivePreset" />

		<!-- The signature "two lines": fixed, full-height vertical rules with rotated-text CTAs breaking each line. -->
		<RouterLink
			:to="{ name: 'home' }"
			class="fixed top-0 h-full z-30 flex flex-col items-center gap-4 cursor-pointer group"
			:class="[entranceClass, firstLinePosition]"
		>
			<span class="w-px flex-1 bg-white/25 group-hover:bg-white/60 transition-colors" aria-hidden="true" />
			<div
				class="flex flex-col items-center gap-4 shrink-0"
				style="writing-mode: vertical-rl; text-orientation: sideways; transform: rotate(180deg)"
			>
				<span class="text-2xs uppercase tracking-[0.3em] text-white/70 group-hover:text-white transition-colors">
					{{ $t("landing.meridian.explore_caption") }}
				</span>
				<span class="text-3xl uppercase tracking-widest font-light text-white group-hover:tracking-[0.15em] transition-all duration-300">
					{{ exploreLabel }}
				</span>
			</div>
			<span class="w-px flex-1 bg-white/25 group-hover:bg-white/60 transition-colors" aria-hidden="true" />
		</RouterLink>

		<RouterLink
			v-if="showContactLine"
			:to="{ name: 'contact' }"
			class="fixed top-0 left-2/3 -translate-x-1/2 h-full z-30 flex flex-col items-center gap-4 cursor-pointer group"
			:class="entranceClass"
		>
			<span class="w-px flex-1 bg-white/25 group-hover:bg-white/60 transition-colors" aria-hidden="true" />
			<div
				class="flex flex-col items-center gap-4 shrink-0"
				style="writing-mode: vertical-rl; text-orientation: sideways; transform: rotate(180deg)"
			>
				<span class="text-2xs uppercase tracking-[0.3em] text-white/70 group-hover:text-white transition-colors">
					{{ $t("landing.meridian.contact_caption") }}
				</span>
				<span class="text-3xl uppercase tracking-widest font-light text-white group-hover:tracking-[0.15em] transition-all duration-300">
					{{ $t("landing.contact") }}
				</span>
			</div>
			<span class="w-px flex-1 bg-white/25 group-hover:bg-white/60 transition-colors" aria-hidden="true" />
		</RouterLink>

		<LandingFooter
			:footer-data="data.footer"
			:links="data.links"
			:animated="effectivePreset !== 'none'"
			:no-intro-delay="!data.intro_screen_enabled"
		/>
	</main>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink } from "vue-router";
import LandingFooter from "@/v8/components/footers/LandingFooter.vue";
import LandingIntroScreen from "@/v8/components/landing/LandingIntroScreen.vue";
import { useLandingAnimation } from "@/v8/composables/landing/useLandingAnimation";
import { useLandingBackgroundOrientation, type LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	previewOrientation?: LandingPreviewOrientation;
}>();

const landingData = toRef(props, "data");

const { effectivePreset } = useLandingAnimation(landingData);
const { landscapeImageClass, portraitImageClass } = useLandingBackgroundOrientation(toRef(props, "previewOrientation"));

const entranceClass = computed(() => {
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

const navLinks = computed(() => props.data.links.filter((link) => link.placement === "nav" || link.placement === "both"));

const exploreLabel = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.meridian.explore_label")));

// Without a contact form there's nothing for the second rail to link to, so it collapses back
// to a single, centered line rather than pointing somewhere meaningless.
const showContactLine = computed(() => props.data.footer.is_contact_form_enabled);
const firstLinePosition = computed(() => (showContactLine.value ? "left-1/3 -translate-x-1/2" : "left-1/2 -translate-x-1/2"));
</script>
