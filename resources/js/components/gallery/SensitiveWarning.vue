<template>
	<div
		id="sensitive_warning"
		:class="is_nsfw_banner_backdrop_blurred ? 'backdrop-blur-lg' : 'bg-red-950'"
		class="fixed flex flex-col items-center justify-center text-center text-surface-0 text-shadow top-14 left-0 h-full w-full"
	>
		<div v-if="nsfw_banner_override !== ''" v-html="nsfw_banner_override"></div>
		<template v-else>
			<div class="w-full flex justify-center">
				<h1 class="text-xl font-bold border-solid border-b-2 border-white mb-3 w-max">{{ $t("lychee.NSFW_HEADER") }}</h1>
			</div>
			<p class="text-base">{{ $t("lychee.NSFW_EXPLANATION") }}</p>
			<p class="text-base">{{ $t("lychee.TAP_CONSENT") }}</p>
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
