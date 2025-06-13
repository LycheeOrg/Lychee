<template>
	<LoadingProgress v-model:loading="isLoading" />
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 bg-surface-900 mb-8">
			<template #start>
				<OpenLeftMenu />
			</template>

			<template #center>
				<span class="text-lg font-semibold">{{ title }}</span>
			</template>

			<template #end> </template>
		</Toolbar>
		<div class="flex flex-col items-center gap-16 mb-16">
			<div
				v-for="album in albums"
				:key="`album-${album.id}`"
				class="max-w-lg shadow-2xl rounded-b-2xl bg-gradient-to-b from-surface-800 to-surface-800"
			>
				<RouterLink :to="{ name: 'album', params: { albumId: album.id } }">
					<div class="flex flex-col items-center">
						<img
							:src="album.photos[0].size_variants.medium?.url ?? album.photos[0].size_variants.small?.url ?? ''"
							:alt="album.pre_formatted_data.title"
							class="w-full h-96 object-cover rounded-t-2xl"
						/>
					</div>
				</RouterLink>
				<div class="w-full overflow-x-scroll flex mt-1 gap-1">
					<div v-for="photo in album.photos" :key="`album-${album.id}-photo-${photo.id}`" class="block shrink-0">
						<img :src="photo.size_variants.thumb?.url ?? ''" :alt="photo.title" class="w-24 h-24 object-cover" />
					</div>
				</div>
				<div class="p-6">
					<h3 class="text-xl font-semibold text-surface-0">{{ album.pre_formatted_data.title }}</h3>
					<p class="text-sm text-muted-color">{{ album.pre_formatted_data.created_at }}</p>
					<p
						class="text-sm text-muted-color prose dark:prose-invert my-4"
						v-if="album.pre_formatted_data.description"
						v-html="album.pre_formatted_data.description"
					></p>
				</div>
			</div>
			<div class="sentinel" ref="sentinel" v-if="currentPage < lastPage"></div>
		</div>
		<ProgressSpinner class="flex justify-center" v-if="isLoading && !isTouchDevice()" />
		<GalleryFooter v-once />
		<ScrollTop target="parent" />
	</div>
</template>
<script setup lang="ts">
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import FlowService from "@/services/flow-service";
import { useAuthStore } from "@/stores/Auth";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useIntersectionObserver } from "@vueuse/core";
import { storeToRefs } from "pinia";
import ProgressSpinner from "primevue/progressspinner";
import ScrollTop from "primevue/scrolltop";
import Toolbar from "primevue/toolbar";
import { onUnmounted } from "vue";
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const leftMenuStore = useLeftMenuStateStore();
const router = useRouter();

lycheeStore.init();

const { is_full_screen, is_login_open, is_upload_visible, list_upload_files, is_webauthn_open } = storeToRefs(togglableStore);
const { are_nsfw_visible, title } = storeToRefs(lycheeStore);

const isLoading = ref(true);
const albums = ref<App.Http.Resources.Flow.FlowItemResource[]>([]);
const config = ref<App.Http.Resources.Flow.InitResource | undefined>(undefined);

const currentPage = ref(1);
const lastPage = ref(0);

const sentinel = ref(null);

function load() {
	isLoading.value = true;
	FlowService.get(currentPage.value).then((data) => {
		isLoading.value = false;
		albums.value.push(...data.data.albums);
		console.log("Flow albums:", albums.value);
		currentPage.value = data.data.current_page;
		lastPage.value = data.data.last_page;
	});
}

function registerSentinel() {
	const { stop } = useIntersectionObserver(sentinel, ([{ isIntersecting }]) => {
		if (isIntersecting && !isLoading.value) {
			console.log("Sentinel intersected, loading more albums...");
			if (currentPage.value < lastPage.value) {
				currentPage.value++;
				load();
			}
		}
	});

	return stop;
}

load();
const stopObserver = registerSentinel();

onUnmounted(() => stopObserver());
</script>
