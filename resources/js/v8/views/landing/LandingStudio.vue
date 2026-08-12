<template>
	<main class="relative w-full min-h-screen bg-black text-white flex flex-col">
		<img
			v-if="hasBackground"
			:src="data.landing_background_landscape"
			alt=""
			class="absolute inset-0 w-full h-full object-cover opacity-50"
			:class="landscapeImageClass"
		/>
		<img
			v-if="hasBackground"
			:src="data.landing_background_portrait"
			alt=""
			class="absolute inset-0 w-full h-full object-cover opacity-50"
			:class="portraitImageClass"
		/>
		<div v-if="hasBackground" class="absolute inset-0 bg-black/40" />

		<div id="logo" class="relative p-6">
			<img v-if="data.landing_header_logo !== ''" :src="data.landing_header_logo" alt="logo" class="h-10 object-contain" />
		</div>

		<div class="relative flex-1 flex flex-col items-center justify-center text-center px-6" :class="entranceClass">
			<h1 class="text-2xl md:text-3xl font-bold uppercase" :style="heroTextStyle">{{ $t("landing.studio.welcome_back") }}</h1>
			<p v-if="data.landing_subtitle !== ''" class="mt-2 text-sm md:text-base" :style="heroTextStyle">{{ data.landing_subtitle }}</p>
			<p v-if="showAbout" class="mt-4 max-w-lg text-sm text-muted prose prose-invert prose-sm" v-html="data.about_text" />

			<RouterLink :to="{ name: 'home' }" class="mt-8 text-xs uppercase text-muted hover:text-white transition-colors">
				{{ $t("landing.view_public_gallery") }}
			</RouterLink>
		</div>

		<div :style="ctaStyle">
			<RouterLink
				:to="{ name: 'login' }"
				class="inline-block px-10 py-3 bg-white text-black uppercase text-sm tracking-wide hover:scale-105 transition-transform"
			>
				{{ primaryCtaText }}
			</RouterLink>
		</div>

		<footer class="relative pb-8 text-center">
			<div v-if="data.footer.footer_show_social_media" class="flex justify-center gap-5 mb-3">
				<a
					v-if="data.footer.sm_facebook_url !== ''"
					id="facebook"
					:href="data.footer.sm_facebook_url"
					target="_blank"
					rel="noopener"
					class="socials text-xl hover:text-muted transition-colors"
				/>
				<a
					v-if="data.footer.sm_flickr_url !== ''"
					id="flickr"
					:href="data.footer.sm_flickr_url"
					target="_blank"
					rel="noopener"
					class="socials text-xl hover:text-muted transition-colors"
				/>
				<a
					v-if="data.footer.sm_twitter_url !== ''"
					id="twitter"
					:href="data.footer.sm_twitter_url"
					target="_blank"
					rel="noopener"
					class="socials text-xl hover:text-muted transition-colors"
				/>
				<a
					v-if="data.footer.sm_instagram_url !== ''"
					id="instagram"
					:href="data.footer.sm_instagram_url"
					target="_blank"
					rel="noopener"
					class="socials text-xl hover:text-muted transition-colors"
				/>
				<a
					v-if="data.footer.sm_youtube_url !== ''"
					id="youtube"
					:href="data.footer.sm_youtube_url"
					target="_blank"
					rel="noopener"
					class="socials text-xl hover:text-muted transition-colors"
				/>
			</div>
			<div v-if="footerLinks.length > 0" class="flex justify-center gap-4 text-xs uppercase">
				<a
					v-for="link in footerLinks"
					:key="link.id"
					:href="link.url"
					:target="link.open_in_new_tab ? '_blank' : undefined"
					:rel="link.open_in_new_tab ? 'noopener' : undefined"
					class="hover:text-muted transition-colors"
				>
					{{ link.label }}
				</a>
			</div>
		</footer>

		<LandingIntroScreen :data="data" :effective-preset="effectivePreset" />
	</main>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink } from "vue-router";
import LandingIntroScreen from "@/v8/components/landing/LandingIntroScreen.vue";
import { useLandingAnimation } from "@/v8/composables/landing/useLandingAnimation";
import { useLandingCtaPosition } from "@/v8/composables/landing/useLandingCtaPosition";
import { useLandingBackgroundOrientation, type LandingPreviewOrientation } from "@/v8/composables/landing/useLandingBackgroundOrientation";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
	previewOrientation?: LandingPreviewOrientation;
}>();

const { landscapeImageClass, portraitImageClass } = useLandingBackgroundOrientation(toRef(props, "previewOrientation"));

const landingData = toRef(props, "data");

const { effectivePreset } = useLandingAnimation(landingData);
const { ctaStyle } = useLandingCtaPosition(landingData);

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

const hasBackground = computed(() => props.data.landing_background_landscape !== "" || props.data.landing_background_portrait !== "");

const primaryCtaText = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.client_login")));

const heroTextStyle = computed(() => ({
	color: props.data.hero_text_color !== "" ? props.data.hero_text_color : "#ffffff",
	opacity: props.data.hero_text_opacity / 100,
}));

const showAbout = computed(() => props.data.about_enabled && props.data.about_text !== "");

const footerLinks = computed(() => props.data.links.filter((link) => link.placement === "footer" || link.placement === "both"));
</script>
