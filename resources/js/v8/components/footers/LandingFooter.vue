<template>
	<div id="footer" class="absolute bg-black z-10 left-0 right-0 bottom-0 text-center py-1 px-0 overflow-clip">
		<div
			id="home_socials"
			class="fixed bottom-8 left-0 right-0 text-center z-10"
			:class="{ 'animate-landingAnimateUp': props.animated, 'no-intro-delay': props.noIntroDelay }"
		>
			<a
				v-if="props.footerData.sm_facebook_url !== ''"
				id="facebook"
				:href="props.footerData.sm_facebook_url"
				class="inline-block hover:scale-150 hover:text-muted transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_flickr_url !== ''"
				id="flickr"
				:href="props.footerData.sm_flickr_url"
				class="inline-block hover:scale-150 hover:text-muted transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_twitter_url !== ''"
				id="twitter"
				:href="props.footerData.sm_twitter_url"
				class="inline-block hover:scale-150 hover:text-muted transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_instagram_url !== ''"
				id="instagram"
				:href="props.footerData.sm_instagram_url"
				class="inline-block hover:scale-150 hover:text-muted transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_youtube_url !== ''"
				id="youtube"
				:href="props.footerData.sm_youtube_url"
				class="inline-block hover:scale-150 hover:text-muted transition-all ease-in-out duration-300 text-white socials text-2xl my-4 mx-5 socialicons"
				target="_blank"
				rel="noopener"
			></a>
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
				<RouterLink
					v-if="link.is_built_in"
					:to="{ name: link.url }"
					class="uppercase hover:text-muted transition-all ease-in-out duration-300"
				>
					{{ link.label }}
				</RouterLink>
				<a
					v-else
					:href="link.url"
					:target="link.open_in_new_tab ? '_blank' : undefined"
					:rel="link.open_in_new_tab ? 'noopener' : undefined"
					class="uppercase hover:text-muted transition-all ease-in-out duration-300"
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

type FooterProps = {
	footerData: App.Http.Resources.GalleryConfigs.FooterConfig;
	links?: App.Http.Resources.GalleryConfigs.LandingLinkEmbedResource[];
	noIntroDelay?: boolean;
	animated?: boolean;
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
