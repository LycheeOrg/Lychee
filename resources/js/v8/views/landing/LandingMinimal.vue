<template>
	<main class="w-full min-h-screen bg-black text-white flex flex-col">
		<div class="flex-1 flex items-center justify-center px-6">
			<div class="max-w-md w-full text-center" :class="entranceClass">
				<img v-if="data.landing_logo !== ''" :src="data.landing_logo" alt="logo" class="mx-auto max-h-24 max-w-xs object-contain mb-6" />
				<template v-else>
					<h1 class="text-2xl font-bold uppercase" :style="heroTextStyle">{{ data.landing_title }}</h1>
					<p class="mt-2 text-sm" :style="heroTextStyle">{{ data.landing_subtitle }}</p>
				</template>

				<p v-if="showAbout" class="mt-6 text-sm text-muted prose prose-invert prose-sm max-w-none" v-html="data.about_text" />

				<RouterLink
					:to="{ name: 'home' }"
					class="mt-8 inline-block px-8 py-3 bg-white text-black uppercase text-sm tracking-wide hover:scale-105 transition-transform"
				>
					{{ ctaText }}
				</RouterLink>
			</div>
		</div>

		<footer class="pb-8 text-center">
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
			<div class="flex justify-center gap-4 text-xs uppercase">
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
				<RouterLink v-if="data.footer.is_contact_form_enabled" :to="{ name: 'contact' }" class="hover:text-muted transition-colors">
					{{ $t("landing.contact") }}
				</RouterLink>
			</div>
			<p v-if="data.footer.footer_show_copyright" class="mt-3 text-3xs uppercase text-muted">{{ data.footer.copyright }}</p>
		</footer>
	</main>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import { RouterLink } from "vue-router";
import { useLandingAnimation } from "@/v8/composables/useLandingAnimation";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
}>();

const { effectivePreset } = useLandingAnimation(toRef(() => props.data.animation_preset));

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

const ctaText = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.access_gallery")));

const heroTextStyle = computed(() => ({
	color: props.data.hero_text_color !== "" ? props.data.hero_text_color : "#ffffff",
	opacity: props.data.hero_text_opacity / 100,
}));

const showAbout = computed(() => props.data.about_enabled && props.data.about_text !== "");

const footerLinks = computed(() => props.data.links.filter((link) => link.placement === "footer" || link.placement === "both"));
</script>
