<template>
	<!-- scrollingSocials renders the icons as a sibling here, above #footer, rather than nested
	     inside it (below) - #footer's own bg-black box is sized to its text content alone on every
	     other layout, so nesting an in-flow icon row inside it there would inflate that black bar
	     well past its usual thin strip. Sitting above it instead keeps #footer's height unchanged. -->
	<div
		v-if="props.scrollingSocials"
		id="home_socials"
		class="relative mb-2 text-center z-10"
		:class="{ 'animate-landingAnimateUp': props.animated, 'no-intro-delay': props.noIntroDelay }"
	>
		<SocialMediaLinks
			:footer-data="props.footerData"
			:link-class="[
				'inline-block hover:scale-150 transition-all ease-in-out duration-300 text-white text-2xl my-4 mx-5',
				{ 'opacity-50 hover:opacity-100': !!props.dimIconsUntilHover },
			]"
		/>
	</div>
	<div id="footer" class="absolute bg-black z-10 left-0 right-0 bottom-0 text-center py-1 px-0 overflow-clip">
		<div
			v-if="!props.scrollingSocials"
			id="home_socials"
			class="fixed bottom-8 left-0 right-0 text-center z-10"
			:class="{ 'animate-landingAnimateUp': props.animated, 'no-intro-delay': props.noIntroDelay }"
		>
			<SocialMediaLinks
				:footer-data="props.footerData"
				:link-class="[
					'inline-block hover:scale-150 transition-all ease-in-out duration-300 text-white text-2xl my-4 mx-5',
					{ 'opacity-50 hover:opacity-100': !!props.dimIconsUntilHover },
				]"
			/>
		</div>
		<p
			v-if="props.footerData.footer_show_copyright"
			class="home_copyright uppercase text-white text-3xs font-normal"
			:class="{ 'animate-landingAnimateUp': props.animated, 'no-intro-delay': props.noIntroDelay }"
		>
			{{ props.footerData.copyright }}
		</p>
		<p
			v-if="props.footerData.footer_additional_text !== ''"
			class="personal_text text-white text-3xs font-normal"
			:class="{ 'animate-landingAnimateUp': props.animated, 'no-intro-delay': props.noIntroDelay }"
			v-html="props.footerData.footer_additional_text"
		></p>
		<p
			v-if="footerLinks.length > 0"
			class="text-white text-3xs font-normal space-x-4"
			:class="{ 'animate-landingAnimateUp': props.animated, 'no-intro-delay': props.noIntroDelay }"
		>
			<template v-for="link in footerLinks" :key="link.id">
				<RouterLink v-if="link.is_built_in" :to="{ name: link.url }" class="uppercase transition-all ease-in-out duration-300">
					{{ link.label }}
				</RouterLink>
				<a
					v-else
					:href="link.url"
					:target="link.open_in_new_tab ? '_blank' : undefined"
					:rel="link.open_in_new_tab ? 'noopener' : undefined"
					class="uppercase transition-all ease-in-out duration-300"
				>
					{{ link.label }}
				</a>
			</template>
		</p>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { RouterLink } from "vue-router";
import SocialMediaLinks from "@/v8/components/footers/SocialMediaLinks.vue";

type FooterProps = {
	footerData: App.Http.Resources.GalleryConfigs.FooterConfig;
	links?: App.Http.Resources.GalleryConfigs.LandingLinkEmbedResource[];
	noIntroDelay?: boolean;
	animated?: boolean;
	dimIconsUntilHover?: boolean;
	// Fixed-viewport layouts (Classic/Meridian/Studio) float the social icons a fixed distance
	// above the true screen bottom, always visible - correct there since the whole page is one
	// screen with nothing to scroll. Portfolio actually scrolls, so a viewport-pinned icon row
	// permanently covers in-page content (e.g. the hero's scroll-down cue); this flows the icons
	// into the footer's own document position instead, so they only appear once you scroll there.
	scrollingSocials?: boolean;
};

const props = defineProps<FooterProps>();

const footerLinks = computed(() => (props.links ?? []).filter((link) => link.placement === "footer" || link.placement === "both"));
</script>
<style lang="css" scoped>
.animate-landingAnimateUp {
	opacity: 0;
	translate: translateY(300px);
	animation-name: landingAnimateUp;
	animation-duration: 2s;
	animation-timing-function: ease-in-out;
	animation-delay: 3s;
	animation-direction: forwards;
	animation-fill-mode: forwards;
}

@keyframes landingAnimateUp {
	0% {
		opacity: 0;
		transform: translateY(300px);
	}
	100% {
		opacity: 1;
		transform: translateY(0px);
	}
}

.no-intro-delay {
	animation-delay: 0s !important;
}
</style>
