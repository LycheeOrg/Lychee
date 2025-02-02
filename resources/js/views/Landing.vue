<template>
	<main v-if="initdata" id="landing" class="w-screen h-screen bg-black roboto overflow-hidden">
		<div id="header" class="fixed top-0 left-0 right-0 z-50 overflow-y-hidden">
			<div id="logo" class="float-left p-4 text-surface-0 animate-landingAnimateDown">
				<a href="#">
					<h1 class="text-lg font-bold uppercase text-center roboto">
						{{ initdata.landing_title }}
						<span class="p-0 text-2xs block font-thin tracking-wide leading-[0]">{{ initdata.landing_subtitle }}</span>
					</h1>
				</a>
			</div>
		</div>

		<div id="menu_wrap" class="fixed top-0 right-0 z-50 w-4/5 overflow-y-hidden">
			<div id="menu" class="w-full animate-landingAnimateDown">
				<ul class="menu list-none">
					<li class="menu-item relative block float-right pt-6 pb-5 px-3">
						<RouterLink to="/gallery" class="cursor-pointer block text-xs uppercase font-normal text-surface-0 hover:text-muted-color">
							{{ $t("landing.gallery") }}
						</RouterLink>
					</li>
				</ul>
			</div>
		</div>

		<div
			id="intro"
			:class="{ hidden: !introVisible }"
			class="z-50 bg-black fixed flex align-middle justify-center left-0 right-0 top-0 bottom-0 animate-landingIntroFadeOut"
		>
			<div id="intro_content" class="self-center">
				<h1 class="text-center text-2xl text-surface-0 uppercase font-extralight animate-landingIntroPopIn">
					{{ initdata.landing_title }}
				</h1>
				<h2>
					<span class="text-center text-base text-muted-color uppercase font-extralight animate-landingIntroPopIn">
						{{ initdata.landing_subtitle }}
					</span>
				</h2>
			</div>
		</div>

		<div id="slides" class="bg-black absolute overflow-hidden left-0 top-0 w-screen h-[98vh]">
			<div class="slides-container w-full h-full opacity-0 animate-landingSlidesPopIn">
				<ul class="list-none">
					<li class="w-full h-full">
						<img class="w-full h-full object-cover absolute top-0 left-0" :src="initdata.landing_background" alt="landing image" />
					</li>
				</ul>
			</div>
			<div class="flex w-full h-1/2 absolute top-1/2 left-0 items-center justify-center animate-landingEnterPopIn opacity-0">
				<span class="text-transparent uppercase text-3xl filter-shadow-darker py-10 px-40">{{ $t("landing.access_gallery") }}</span>
			</div>
			<div class="flex w-full h-1/2 absolute top-1/3 md:top-1/2 left-0 items-center justify-center animate-landingEnterPopIn opacity-0">
				<RouterLink
					to="/gallery"
					class="cursor-pointer block text-2xl uppercase text-surface-0 hover:scale-125 transition-all duration-300 p-10 filter-shadow text-center"
				>
					{{ $t("landing.access_gallery") }}<br class="md:hidden" />
					<i class="pi pi-angle-right animate-pulseTo0 text-2xl animate-infinite"></i>
					<i class="pi pi-angle-right animate-pulseTo0 text-2xl animate-delay-500 animate-infinite -ml-1"></i>
					<i class="pi pi-angle-right animate-pulseTo0 text-2xl animate-delay-1000 animate-infinite -ml-1"></i>
				</RouterLink>
			</div>
		</div>
		<LandingFooter :footerData="initdata.footer" />
	</main>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import InitService from "@/services/init-service";
import LandingFooter from "@/components/footers/LandingFooter.vue";

const introVisible = ref(true);

const initdata = ref<App.Http.Resources.GalleryConfigs.LandingPageResource | undefined>(undefined);
const router = useRouter();

InitService.fetchLandingData().then((data) => {
	if (data.data.landing_page_enable === false) {
		router.push("/gallery");
	} else {
		initdata.value = data.data;
		setTimeout(() => (introVisible.value = false), 4000);
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
