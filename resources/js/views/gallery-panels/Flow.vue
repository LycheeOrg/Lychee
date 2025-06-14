<template>
	<LoadingProgress v-model:loading="isLoading" />
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 bg-surface-900 mb-8">
			<template #start>
				<OpenLeftMenu />
			</template>

			<template #center>
				<span class="text-lg font-semibold text-center hidden md:block">{{ title }}</span>
			</template>

			<template #end> </template>
		</Toolbar>
		<div class="absolute top-0 left-1/2 text-center text-lg font-semibold text-surface-0 w-xs -translate-x-1/2 md:hidden">
			{{ title }}
		</div>
		<div class="flex flex-col items-center gap-16 mb-16 px-8" v-if="config !== undefined">
			<TransitionGroup name="slide-fade">
				<AlbumCard v-for="album in albums" :key="`album-${album.id}`" :album="album" :config="config" />
			</TransitionGroup>
			<div v-if="albums && albums.length === 0" class="h-[70vh] text-muted-color flex items-center">No content.</div>
			<div class="sentinel" ref="sentinel" v-if="currentPage < lastPage"></div>
		</div>
		<ProgressSpinner class="flex justify-center" v-if="isLoading && !isTouchDevice()" />
		<GalleryFooter v-once />
		<ScrollTop target="parent" />
	</div>
</template>
<script setup lang="ts">
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import AlbumCard from "@/components/gallery/flowModule/AlbumCard.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import FlowService from "@/services/flow-service";
import { useAuthStore } from "@/stores/Auth";
import { useFlowStateStore } from "@/stores/FlowState";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useIntersectionObserver } from "@vueuse/core";
import { storeToRefs } from "pinia";
import ProgressSpinner from "primevue/progressspinner";
import ScrollTop from "primevue/scrolltop";
import Toolbar from "primevue/toolbar";
import { onMounted } from "vue";
import { onUnmounted } from "vue";
import { ref } from "vue";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const flowState = useFlowStateStore();
const router = useRouter();

lycheeStore.init();

const leftMenuStore = useLeftMenuStateStore();
const { is_full_screen } = storeToRefs(togglableStore);
const { title } = storeToRefs(lycheeStore);
const { are_nsfw_blurred, are_nsfw_consented } = storeToRefs(flowState);

const isLoading = ref(true);
const albums = ref<App.Http.Resources.Flow.FlowItemResource[] | undefined>(undefined);
const config = ref<App.Http.Resources.Flow.InitResource | undefined>(undefined);
const currentPage = ref(1);
const lastPage = ref(0);
const sentinel = ref(null);
let stopObserver = null;

function load() {
	isLoading.value = true;
	FlowService.get(currentPage.value).then((data) => {
		isLoading.value = false;
		if (albums.value === undefined) {
			albums.value = [];
		}
		albums.value.push(...data.data.albums);
		currentPage.value = data.data.current_page;
		lastPage.value = data.data.last_page;
	});
}

function registerSentinel() {
	const { stop } = useIntersectionObserver(sentinel, ([{ isIntersecting }]) => {
		if (isIntersecting && !isLoading.value && config.value !== undefined) {
			console.log("Sentinel intersected, loading more albums...");
			if (currentPage.value < lastPage.value) {
				currentPage.value++;
				load();
			}
		}
	});

	return stop;
}

onMounted(async () => {
	are_nsfw_consented.value = false;

	leftMenuStore.left_menu_open = false;
	const user = await auth.getUser();

	await FlowService.init().then((response) => {
		config.value = response.data;
		are_nsfw_blurred.value = response.data.is_blur_nsfw_enabled;
	});

	if (user.id === null && !config.value?.is_mod_flow_enabled) {
		router.push({ name: "gallery" });
		return;
	}

	load();
});

stopObserver = registerSentinel();

onUnmounted(() => stopObserver());
</script>
<style>
/*
  Enter and leave animations can use different
  durations and timing functions.
*/
.slide-fade-enter-active,
.slide-fade-leave-active {
	transition: all 0.3s ease-out;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
	transform: translateY(20px);
	opacity: 0;
}
</style>
