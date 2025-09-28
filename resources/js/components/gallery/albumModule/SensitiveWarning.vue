<template>
	<div
		v-if="albumId !== undefined && nsfwConsentedStore.hasConsented(albumId) === false"
		id="sensitive_warning"
		:class="{
			'fixed flex flex-col items-center justify-center text-center text-surface-0 text-shadow top-14 left-0 h-full w-full': true,
			'bg-red-950': !is_nsfw_banner_backdrop_blurred,
			'backdrop-blur-lg': is_nsfw_banner_backdrop_blurred,
		}"
		@click="nsfwConsentedStore.consent(albumId)"
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
import { useAlbumStore } from "@/stores/AlbumState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useNsfwConsentedStore } from "@/stores/NsfwConsentedState";
import { storeToRefs } from "pinia";
import { computed, onMounted } from "vue";

const lycheeStore = useLycheeStateStore();
const nsfwConsentedStore = useNsfwConsentedStore();
const albumStore = useAlbumStore();
// Fetch the id of the current album
const albumId = computed(() => albumStore.albumId);

const { nsfw_banner_override, is_nsfw_banner_backdrop_blurred } = storeToRefs(lycheeStore);

onMounted(() => {
	lycheeStore.load();
});
</script>
