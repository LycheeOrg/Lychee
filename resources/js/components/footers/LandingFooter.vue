<template>
	<div id="footer" class="absolute bg-black z-10 left-0 right-0 bottom-0 text-center py-1 px-0 overflow-clip">
		<div id="home_socials" class="fixed bottom-8 left-0 right-0 text-center z-10 animate-landingAnimateUp">
			<a
				v-if="props.footerData.sm_facebook_url !== ''"
				:href="props.footerData.sm_facebook_url"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="facebook"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_flickr_url !== ''"
				:href="props.footerData.sm_flickr_url"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="flickr"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_twitter_url !== ''"
				:href="props.footerData.sm_twitter_url"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="twitter"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_instagram_url !== ''"
				:href="props.footerData.sm_instagram_url"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="instagram"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.footerData.sm_youtube_url !== ''"
				:href="props.footerData.sm_youtube_url"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="youtube"
				target="_blank"
				rel="noopener"
			></a>
		</div>
		<p
			v-if="props.footerData.footer_show_copyright"
			class="home_copyright uppercase text-surface-0 text-3xs font-normal animate-landingAnimateUp"
		>
			{{ props.footerData.copyright }}
		</p>
		<p
			v-if="props.footerData.footer_additional_text !== ''"
			class="personal_text text-surface-0 text-3xs font-normal animate-landingAnimateUp"
			v-html="props.footerData.footer_additional_text"
		></p>
	</div>
</template>
<script setup lang="ts">
type FooterProps = {
	footerData: App.Http.Resources.GalleryConfigs.FooterConfig;
};

const props = defineProps<FooterProps>();
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
</style>
