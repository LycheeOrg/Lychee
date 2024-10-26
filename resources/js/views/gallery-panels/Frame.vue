<template>
	<div class="h-screen w-screen">
		<img v-if="imgSrc !== ''" alt="image background" class="absolute w-screen h-screen object-cover blur-lg object-center" :src="imgSrc" />
		<div class="w-screen h-screen flex justify-center items-center flex-wrap bg-repeat bg-noise">
			<img
				v-if="imgSrc !== ''"
				alt="Random Image"
				class="h-[95%] w-[95%] object-contain filter drop-shadow-black"
				:src="imgSrc"
				:srcset="imgSrcset"
			/>
		</div>
		<div id="shutter" class="absolute w-screen h-dvh bg-surface-950 transition-opacity duration-1000 ease-in-out top-0 left-0"></div>
		<div class="absolute top-0 left-0 p-3">
			<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import AlbumService from "@/services/album-service";
import { onKeyStroke } from "@vueuse/core";
import Button from "primevue/button";
import { ref } from "vue";
import { useRouter } from "vue-router";

const props = defineProps<{
	albumid?: string;
}>();

const router = useRouter();
const imgSrc = ref("");
const imgSrcset = ref("");
const refreshTimeout = ref(5);

const is_slideshow_active = ref(false);

function getNext() {
	AlbumService.frame(props.albumid ?? null).then((response) => {
		imgSrc.value = response.data.src;
		imgSrcset.value = response.data.srcset;
	});
}

const { slideshow, clearTimeouts } = useSlideshowFunction(1000, is_slideshow_active, refreshTimeout, getNext, undefined);

function start() {
	AlbumService.frame(props.albumid ?? null).then((response) => {
		refreshTimeout.value = response.data.timeout;
		getNext();
		slideshow();
	});
}

start();

function goBack() {
	clearTimeouts();

	if (props.albumid !== undefined) {
		router.push({ name: "album", params: { albumid: props.albumid } });
	} else {
		router.push({ name: "gallery" });
	}
}

onKeyStroke("Escape", () => {
	goBack();
});
</script>
