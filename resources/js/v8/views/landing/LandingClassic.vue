<template>
	<main id="landing" class="w-screen h-screen bg-black overflow-hidden">
		<div id="header" class="fixed top-0 left-0 right-0 z-50 overflow-y-hidden">
			<div id="logo" class="float-left p-4 text-white" :class="entranceDownClass">
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
			<div id="menu" class="w-full" :class="entranceDownClass">
				<ul class="menu list-none">
					<li v-for="link in navLinks" :key="link.id" class="menu-item relative block float-right pt-6 pb-5 px-3">
						<a
							:href="link.url"
							:target="link.open_in_new_tab ? '_blank' : undefined"
							:rel="link.open_in_new_tab ? 'noopener' : undefined"
							class="cursor-pointer block text-xs uppercase font-normal text-white hover:text-muted"
						>
							{{ link.label }}
						</a>
					</li>
					<li class="menu-item relative block float-right pt-6 pb-5 px-3">
						<RouterLink :to="{ name: 'home' }" class="cursor-pointer block text-xs uppercase font-normal text-white hover:text-muted">
							{{ $t("landing.gallery") }}
						</RouterLink>
					</li>
				</ul>
			</div>
		</div>

		<div
			v-if="data.intro_screen_enabled"
			id="intro"
			:class="{ hidden: !introVisible, 'animate-landingIntroFadeOut': effectivePreset !== 'none' }"
			class="z-50 bg-black fixed flex align-middle justify-center left-0 right-0 top-0 bottom-0"
		>
			<div id="intro_content" class="self-center">
				<img
					v-if="data.landing_logo !== ''"
					id="landing-title-logo"
					:src="data.landing_logo"
					alt="logo"
					class="max-h-32 max-w-xs object-contain"
					:class="{ 'animate-landingIntroPopIn': effectivePreset !== 'none' }"
				/>
				<template v-else>
					<h1
						class="text-center text-2xl uppercase font-extralight"
						:class="{ 'animate-landingIntroPopIn': effectivePreset !== 'none' }"
						:style="heroTextStyle"
					>
						{{ data.landing_title }}
					</h1>
					<h2>
						<span
							class="text-center text-base uppercase font-extralight block"
							:class="{ 'animate-landingIntroPopIn': effectivePreset !== 'none' }"
							:style="heroTextStyle"
						>
							{{ data.landing_subtitle }}
						</span>
					</h2>
				</template>
			</div>
		</div>

		<div id="slides" class="bg-black absolute overflow-hidden left-0 top-0 w-screen h-[98vh]">
			<div class="slides-container w-full h-full" :class="effectivePreset !== 'none' ? 'opacity-0 animate-landingSlidesPopIn' : ''">
				<ul class="list-none">
					<li class="w-full h-full">
						<img
							class="portrait:hidden w-full h-full object-cover absolute top-0 left-0"
							:src="data.landing_background_landscape"
							alt="landing image"
						/>
						<img
							class="landscape:hidden w-full h-full object-cover absolute top-0 left-0"
							:src="data.landing_background_portrait"
							alt="landing image"
						/>
					</li>
				</ul>
			</div>
			<div class="flex w-full h-full absolute top-0 left-0" :class="positionClasses">
				<div class="relative">
					<span
						class="pointer-events-none absolute inset-0 flex items-center justify-center text-transparent uppercase text-3xl filter-shadow-darker py-10 px-40"
						aria-hidden="true"
					>
						{{ ctaText }}
					</span>
					<RouterLink
						:to="{ name: 'home' }"
						class="cursor-pointer block text-2xl uppercase text-white hover:scale-125 transition-all duration-300 p-10 filter-shadow text-center"
						:class="effectivePreset !== 'none' ? 'animate-landingEnterPopIn opacity-0' : ''"
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
		</div>
		<LandingFooter :footer-data="data.footer" :links="data.links" />
	</main>
</template>
<script setup lang="ts">
import { computed, onMounted, ref, toRef } from "vue";
import { RouterLink } from "vue-router";
import LandingFooter from "@/v8/components/footers/LandingFooter.vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { useLandingTextPosition } from "@/v8/composables/useLandingTextPosition";
import { useLandingAnimation } from "@/v8/composables/useLandingAnimation";
import { trans } from "laravel-vue-i18n";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	data: App.Http.Resources.GalleryConfigs.LandingPageResource;
}>();

const introVisible = ref(true);

const { positionClasses } = useLandingTextPosition(toRef(() => props.data.hero_text_position));
const { effectivePreset } = useLandingAnimation(toRef(() => props.data.animation_preset));

const entranceDownClass = computed(() => (effectivePreset.value !== "none" ? "animate-landingAnimateDown" : ""));

const ctaText = computed(() => (props.data.cta_text !== "" ? props.data.cta_text : trans("landing.access_gallery")));

const navLinks = computed(() => props.data.links.filter((link) => link.placement === "nav" || link.placement === "both"));

const heroTextStyle = computed(() => ({
	color: props.data.hero_text_color !== "" ? props.data.hero_text_color : "#ffffff",
	opacity: props.data.hero_text_opacity / 100,
}));

onMounted(() => {
	if (props.data.intro_screen_enabled) {
		setTimeout(() => (introVisible.value = false), 4000);
	} else {
		introVisible.value = false;
	}
});
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
</style>
