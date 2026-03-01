<template>
	<div v-if="footerData" id="footer" class="w-full flex flex-col justify-end flex-wrap align-bottom self-end text-center py-5 px-0 text-3xs">
		<!--
			Footer Vertically shares space with the content.
			The height of the footer is always the natural height
			of its child elements
			-->
		<div v-if="footerData.footer_show_social_media" id="home_socials" class="w-full text-muted-color text-base space-x-2">
			<a
				v-if="footerData.sm_facebook_url !== ''"
				id="facebook"
				:href="footerData.sm_facebook_url"
				class="socials socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_flickr_url !== ''"
				id="flickr"
				:href="footerData.sm_flickr_url"
				class="socials socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_twitter_url !== ''"
				id="twitter"
				:href="footerData.sm_twitter_url"
				class="socials socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_instagram_url !== ''"
				id="instagram"
				:href="footerData.sm_instagram_url"
				class="socials socialicons"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="footerData.sm_youtube_url !== ''"
				id="youtube"
				:href="footerData.sm_youtube_url"
				class="socials socialicons"
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
				{{ $t("landing.Powered_by_Lychee") }}
			</a>
		</p>
		<p v-if="footerData.is_contact_form_enabled" class="contact_form_link w-full uppercase text-muted-color leading-6 font-normal">
			<a rel="noopener noreferrer" target="_blank" :href="Constants.BASE_URL + '/contact'" class="underline">
				{{ footerData.contact_header ? footerData.contact_header : $t("contact.title") }}
			</a>
		</p>
	</div>
</template>
<script setup lang="ts">
import InitService from "@/services/init-service";
import { ref } from "vue";
import Constants from "@/services/constants";

const footerData = ref<App.Http.Resources.GalleryConfigs.FooterConfig | undefined>(undefined);
InitService.fetchFooter().then((data) => {
	footerData.value = data.data;
});
</script>
