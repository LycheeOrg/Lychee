<template>
	<div
		id="sensitive_warning"
		:class="{
			'fixed flex flex-col items-center justify-center text-center text-surface-0 text-shadow top-14 left-0 h-full w-full': true,
			'bg-red-950': !is_nsfw_banner_backdrop_blurred,
			'backdrop-blur-lg': is_nsfw_banner_backdrop_blurred,
		}"
	>
		<div v-if="nsfw_banner_override !== ''" v-html="nsfw_banner_override"></div>
		<template v-else>
			<div class="w-full flex justify-center">
				<h1 class="text-xl font-bold border-solid border-b-2 border-white mb-3 w-max" v-html="$t('gallery.nsfw.header')" />
			</div>
			<p class="text-base" v-html="$t('gallery.nsfw.description')" />
			<p class="text-base" v-html="$t('gallery.nsfw.consent')" />
		</template>
	</div>
</template>
<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { nsfw_banner_override, is_nsfw_banner_backdrop_blurred } = storeToRefs(lycheeStore);
</script>
