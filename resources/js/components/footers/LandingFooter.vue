<template>
	<div id="footer" class="absolute bg-black z-10 left-0 right-0 bottom-0 text-center py-1 px-0 overflow-clip">
		<div id="home_socials" class="fixed bottom-8 left-0 right-0 text-center z-10 opacity-0 translate-y-[300px] animate-ladningAnimateUp">
			<a
				v-if="props.facebook !== ''"
				:href="props.facebook"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="facebook"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.flickr !== ''"
				:href="props.flickr"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="flickr"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.twitter !== ''"
				:href="props.twitter"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="twitter"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.instagram !== ''"
				:href="props.instagram"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="instagram"
				target="_blank"
				rel="noopener"
			></a>
			<a
				v-if="props.youtube !== ''"
				:href="props.youtube"
				class="inline-block hover:scale-150 hover:text-muted-color transition-all ease-in-out duration-300 text-surface-0 socials text-2xl my-4 mx-5 socialicons"
				id="youtube"
				target="_blank"
				rel="noopener"
			></a>
		</div>
		<p
			v-if="props.show_copy_right"
			class="home_copyright uppercase text-surface-0 text-3xs font-normal translate-y-[300px] animate-ladningAnimateUp"
		>
			{{ copyright }}
		</p>
		<p v-if="props.personalText !== ''" class="personal_text text-surface-0 text-3xs font-normal translate-y-[300px] animate-ladningAnimateUp">
			{{ props.personalText }}
		</p>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";

type FooterProps = {
	show_socials: boolean;
	facebook: string;
	flickr: string;
	twitter: string;
	instagram: string;
	youtube: string;
	show_copy_right: boolean;
	site_owner: string;
	site_copyright_begin: number;
	site_copyright_end: number;
	personalText: string;
};

const props = defineProps<FooterProps>();

const copyright = computed(() => {
	let copyRightYear: string = `${props.site_copyright_begin}`;
	let copyRightYearEnd: string = `${props.site_copyright_end}`;
	if (copyRightYear !== copyRightYearEnd) {
		copyRightYear = copyRightYear + "-" + copyRightYearEnd;
	}
	return sprintf(trans("lychee.FOOTER_COPYRIGHT"), props.site_owner, copyRightYear);
});
</script>
