<template>
	<div id="footer" class="w-full flex flex-col justify-end flex-wrap align-bottom self-end text-center py-5 px-0 text-3xs" v-if="footerData">
		<!--
			Footer Vertically shares space with the content.
			The height of the footer is always the natural height
			of its child elements
			-->
		<div id="home_socials" class="w-full text-muted-color text-base space-x-2" v-if="footerData.footer_show_social_media">
			<a
				v-if="footerData.sm_facebook_url !== ''"
				:href="footerData.sm_facebook_url"
				class="socials socialicons"
				id="facebook"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_flickr_url !== ''"
				:href="footerData.sm_flickr_url"
				class="socials socialicons"
				id="flickr"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_twitter_url !== ''"
				:href="footerData.sm_twitter_url"
				class="socials socialicons"
				id="twitter"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_instagram_url !== ''"
				:href="footerData.sm_instagram_url"
				class="socials socialicons"
				id="instagram"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_youtube_url !== ''"
				:href="footerData.sm_youtube_url"
				class="socials socialicons"
				id="youtube"
				target="_blank"
				rel="noopener"
			></a>
		</div>
		<p
			v-if="footerData.footer_show_copyright && footerData.copyright !== ''"
			class="home_copyright w-full uppercase text-muted-color leading-6 font-normal"
		>
			{{ footerData.copyright }}
		</p>
		<p
			v-if="footerData.footer_additional_text !== ''"
			class="personal_text w-full text-muted-color leading-6 font-normal"
			v-html="footerData.footer_additional_text"
		></p>
		<p class="hosted_by w-full uppercase text-muted-color leading-6 font-normal">
			<a rel="noopener noreferrer" target="_blank" href="https://lycheeorg.dev" tabindex="-1" class="underline">
				{{ $t("landing.hosted_with_lychee") }}
			</a>
		</p>
	</div>
</template>
<script setup lang="ts">
import InitService from "@/services/init-service";
import { ref } from "vue";

const footerData = ref<App.Http.Resources.GalleryConfigs.FooterConfig | undefined>(undefined);
InitService.fetchFooter().then((data) => {
	footerData.value = data.data;
});
</script>
