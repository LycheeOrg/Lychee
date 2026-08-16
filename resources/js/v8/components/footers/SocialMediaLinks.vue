<template>
	<a
		v-if="footerData.sm_facebook_url !== ''"
		:href="footerData.sm_facebook_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="Facebook"
	>
		<UIcon name="social:facebook" />
	</a>
	<a v-if="footerData.sm_flickr_url !== ''" :href="footerData.sm_flickr_url" :class="linkClass" target="_blank" rel="noopener" aria-label="Flickr">
		<UIcon name="social:flickr" />
	</a>
	<a
		v-if="footerData.sm_twitter_url !== ''"
		:href="footerData.sm_twitter_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="Twitter"
	>
		<UIcon name="social:twitter" />
	</a>
	<a
		v-if="footerData.sm_instagram_url !== ''"
		:href="footerData.sm_instagram_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="Instagram"
	>
		<UIcon name="social:instagram" />
	</a>
	<a
		v-if="footerData.sm_youtube_url !== ''"
		:href="footerData.sm_youtube_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="YouTube"
	>
		<UIcon name="social:youtube" />
	</a>
	<a
		v-if="footerData.sm_pinterest_url !== ''"
		:href="footerData.sm_pinterest_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="Pinterest"
	>
		<UIcon name="social:pinterest" />
	</a>
	<a
		v-if="footerData.sm_deviantart_url !== ''"
		:href="footerData.sm_deviantart_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="DeviantArt"
	>
		<UIcon name="social:deviantart" />
	</a>
	<a v-if="footerData.sm_tumblr_url !== ''" :href="footerData.sm_tumblr_url" :class="linkClass" target="_blank" rel="noopener" aria-label="Tumblr">
		<UIcon name="social:tumblr" />
	</a>
	<a v-if="footerData.sm_500px_url !== ''" :href="footerData.sm_500px_url" :class="linkClass" target="_blank" rel="noopener" aria-label="500px">
		<UIcon name="social:500px" />
	</a>
	<a
		v-if="footerData.sm_pixelfeed_url !== ''"
		:href="footerData.sm_pixelfeed_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="Pixelfeed"
	>
		<UIcon name="lucide:aperture" />
	</a>
	<a
		v-if="footerData.sm_discord_url !== ''"
		:href="footerData.sm_discord_url"
		:class="linkClass"
		target="_blank"
		rel="noopener"
		aria-label="Discord"
	>
		<UIcon name="social:discord" />
	</a>
	<a v-if="footerData.sm_reddit_url !== ''" :href="footerData.sm_reddit_url" :class="linkClass" target="_blank" rel="noopener" aria-label="Reddit">
		<UIcon name="social:reddit" />
	</a>
</template>
<script setup lang="ts">
type ClassBinding = string | Record<string, boolean> | ClassBinding[];

defineProps<{
	footerData: App.Http.Resources.GalleryConfigs.FooterConfig;
	linkClass?: ClassBinding;
}>();
</script>
