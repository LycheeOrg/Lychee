<template>
	<main v-if="initdata" id="landing" class="w-screen h-screen bg-black roboto overflow-hidden">
		<div id="header" class="fixed top-0 left-0 right-0 z-50 overflow-y-hidden">
			<div id="logo" class="float-left p-4 text-text-main-0 translate-y-[-300px] opacity-0 animate-ladningAnimateDown">
				<a href="#">
					<h1 class="text-lg font-bold uppercase text-center roboto">
						{{ initdata.config.landing_title }}
						<span class="p-0 text-2xs block font-thin tracking-wide leading-[0]">{{ initdata.config.landing_subtitle }}</span>
					</h1>
				</a>
			</div>
		</div>

		<div id="menu_wrap" class="fixed top-0 right-0 z-50 w-4/5 overflow-y-hidden">
			<div id="menu" class="w-full translate-y-[-300px] opacity-0 animate-ladningAnimateDown">
				<ul class="menu list-none">
					<li class="menu-item relative block float-right pt-6 pb-5 px-3">
						<RouterLink
							to="/gallery"
							class="cursor-pointer block text-xs uppercase font-normal text-text-main-0 hover:text-text-main-400"
							>{{ $t("lychee.GALLERY") }}</RouterLink
						>
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
				<h1 class="text-center text-2xl text-text-main-0 uppercase font-extralight animate-landingIntroPopIn">
					{{ initdata.config.landing_title }}
				</h1>
				<h2>
					<span class="text-center text-base text-text-main-400 uppercase font-extralight animate-landingIntroPopIn">{{
						initdata.config.landing_subtitle
					}}</span>
				</h2>
			</div>
		</div>

		<div id="slides" class="bg-black absolute overflow-hidden left-0 top-0 w-screen h-[98vh]">
			<div class="slides-container w-full h-full opacity-0 animate-landingSlidesPopIn">
				<ul class="list-none">
					<li class="w-full h-full">
						<img class="w-full h-full object-cover absolute top-0 left-0" :src="initdata.config.landing_background" alt="landing image" />
					</li>
				</ul>
			</div>
		</div>
		<LandingFooter
			:show_socials="initdata.config.footer_show_social_media"
			:facebook="initdata.config.sm_facebook_url"
			:flickr="initdata.config.sm_flickr_url"
			:twitter="initdata.config.sm_twitter_url"
			:instagram="initdata.config.sm_instagram_url"
			:youtube="initdata.config.sm_youtube_url"
			:copyright="copyright"
			:personalText="initdata.config.footer_additional_text"
		/>
	</main>
</template>
<script setup lang="ts">
import InitService from "@/services/init-service";
import LandingFooter from "@/components/footers/LandingFooter.vue";
import { Ref, ref } from "vue";
import { RouterLink } from "vue-router";
import { InitializationData } from "@/lycheeOrg/backend";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

const introVisible = ref(true);

const initdata = ref(undefined) as Ref<undefined | InitializationData>;
const copyright = ref("");

InitService.fetchInitData()
	.then((data) => {
		initdata.value = data.data;
		setCopyRight(initdata.value);
		setTimeout(() => (introVisible.value = false), 4000);
	})
	.catch((error) => {
		console.error(error);
	});

function setCopyRight(initdata: InitializationData) {
	if (initdata.config.footer_show_copyright) {
		let copyRightYear: string = `${initdata.config.site_copyright_begin}`;
		let copyRightYearEnd: string = `${initdata.config.site_copyright_end}`;
		if (copyRightYear !== copyRightYearEnd) {
			copyRightYear = copyRightYear + "-" + copyRightYearEnd;
		}
		copyright.value = sprintf(trans("lychee.FOOTER_COPYRIGHT"), initdata.config.site_owner, copyRightYear);
	}
}
</script>
