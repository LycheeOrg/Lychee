<template>
	<Transition name="lychee-loading-fade">
		<div
			v-if="loading"
			class="fixed inset-0 z-50 flex items-center justify-center bg-white/60 dark:bg-black/60 backdrop-blur-2xl"
			role="status"
			:aria-label="$t('dialogs.upload.loading')"
		>
			<LycheeLoadingIcon class="w-24 h-24 sm:w-32 sm:h-32" />
		</div>
	</Transition>
</template>
<script setup lang="ts">
import type { Ref } from "vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";

const loading = defineModel("loading") as Ref<boolean>;
</script>
<style scoped>
.lychee-loading-fade-enter-active,
.lychee-loading-fade-leave-active {
	transition: opacity 1s ease;
}
.lychee-loading-fade-enter-from,
.lychee-loading-fade-leave-to {
	opacity: 0;
}
</style>
