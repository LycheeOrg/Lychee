<template>
	<div class="h-screen w-screen">
		<img v-if="imgSrc !== ''" alt="image background" class="absolute w-screen h-screen object-cover blur-lg object-center" :src="imgSrc" />
		<div class="w-screen h-screen flex justify-center items-center flex-wrap bg-repeat bg-noise">
			<img
				v-if="imgSrc !== ''"
				alt="Random Image"
				class="h-[95%] w-[95%] object-contain filter drop-shadow-black z-10"
				:src="imgSrc"
				:srcset="imgSrcset"
			/>
		</div>
		<div id="shutter" class="absolute w-screen h-dvh bg-neutral-950 transition-opacity duration-1000 ease-in-out top-0 left-0 z-20"></div>
		<FrameOverlays v-if="clock !== null && info !== null" :clock="clock" :info="info" :photo="photo" />
		<div class="absolute top-0 ltr:left-0 rtl:right-0 p-3">
			<GoBack @go-back="goBack" />
		</div>
	</div>
</template>
<script setup lang="ts">
import GoBack from "@/v8/components/headers/GoBack.vue";
import FrameOverlays from "@/v8/components/frame/FrameOverlays.vue";
import { useFrameSlideshow } from "@/v8/composables/frame/frameSlideshow";
import { resolveClock, resolveInfo } from "@/v8/utils/frameOverlay";
import AlbumService from "@/services/album-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { definePanelShortcuts } from "@/v8/composables/usePanelShortcuts";

const props = defineProps<{
	albumId?: string;
}>();

const router = useRouter();
const route = useRoute();
const imgSrc = ref("");
const imgSrcset = ref("");
const refreshTimeout = ref(5);
const leftMenuStore = useLeftMenuStateStore();
const frame = ref<App.Http.Resources.Frame.FrameData | null>(null);

// Admin values with this device's URL overrides on top (FR-088-15).
const clock = computed(() => (frame.value === null ? null : resolveClock(frame.value.clock, route.query)));
const info = computed(() => (frame.value === null ? null : resolveInfo(frame.value.info, route.query)));
const photo = computed(() => frame.value?.photo ?? null);

function getNext() {
	AlbumService.frame(props.albumId ?? null).then((response) => {
		imgSrc.value = response.data.src;
		imgSrcset.value = response.data.srcset;
		frame.value = response.data;
	});
}

const { start: startSlideshow, clearTimeouts } = useFrameSlideshow(1000, refreshTimeout, getNext);

function start() {
	AlbumService.frame(props.albumId ?? null).then((response) => {
		refreshTimeout.value = response.data.timeout;
		getNext();
		startSlideshow();
	});
}

onMounted(() => {
	leftMenuStore.left_menu_open = false;
	const elem = document.getElementsByTagName("body")[0];

	elem.requestFullscreen()
		.then(() => {})
		.catch((err) => console.log(err));

	start();
});

onUnmounted(() => {
	try {
		document.exitFullscreen();
	} catch (_) {
		// Do nothing.
	}
	clearTimeouts();
});

function goBack() {
	clearTimeouts();
	try {
		document.exitFullscreen();
	} catch (_) {
		// Do nothing.
	}

	if (props.albumId !== undefined && props.albumId !== "") {
		router.push({ name: "album", params: { albumId: props.albumId } });
	} else {
		router.push({ name: "gallery" });
	}
}

definePanelShortcuts({
	escape: {
		usingInput: true,
		handler: () => goBack(),
	},
});

onMounted(() => {
	document.documentElement.requestFullscreen();
});
</script>
