<template>
	<div id="footer" class="w-full h-full flex flex-col justify-end flex-wrap align-bottom self-end text-center py-5 px-0 text-3xs">
		<!--
	Footer Vertically shares space with the content.
	The height of the footer is always the natural height
	of its child elements
	-->
		<div id="home_socials" class="w-full" style="display: none" v-if="props.show_socials">
			<a v-if="props.facebook !== ''" :href="props.facebook" class="socialicons" id="facebook" target="_blank" rel="noopener"></a>
			<a v-if="props.flickr !== ''" :href="props.flickr" class="socialicons" id="flickr" target="_blank" rel="noopener"></a>
			<a v-if="props.twitter !== ''" :href="props.twitter" class="socialicons" id="twitter" target="_blank" rel="noopener"></a>
			<a v-if="props.instagram !== ''" :href="props.instagram" class="socialicons" id="instagram" target="_blank" rel="noopener"></a>
			<a v-if="props.youtube !== ''" :href="props.youtube" class="socialicons" id="youtube" target="_blank" rel="noopener"></a>
		</div>
		<p v-if="props.copyright !== ''" class="home_copyright w-full uppercase text-muted-color leading-6 font-normal">{{ props.copyright }}</p>
		<p v-if="props.additionalFooterText !== ''" class="personal_text w-full text-muted-color leading-6 font-normal">
			{{ props.additionalFooterText }}
		</p>
		<p class="hosted_by w-full uppercase text-muted-color leading-6 font-normal">
			<a rel="noopener noreferrer" target="_blank" href="https://LycheeOrg.github.io" tabindex="-1" class="underline">{{
				trans("lychee.HOSTED_WITH_LYCHEE")
			}}</a>
		</p>
	</div>
</template>
<script setup lang="ts">
import { trans } from "laravel-vue-i18n";

type FooterProps = {
	show_socials: boolean;
	facebook: string;
	flickr: string;
	twitter: string;
	instagram: string;
	youtube: string;
	copyright: string;
	additionalFooterText: string;
};

const props = defineProps<FooterProps>();
</script>
