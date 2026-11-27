<template>
	<div class="absolute inset-0 m-auto flex items-center justify-center" style="container-type: size">
		<video-player class="contents" @keydown="onKeydown">
			<video-skin class="block" :style="skinStyle">
				<video :src="props.src" playsinline preload="auto" :autoplay="props.autoplay" :loop="props.loop" @loadedmetadata="onLoadedMetadata">
					Your browser does not support the video tag.
				</video>
			</video-skin>
		</video-player>
	</div>
</template>
<script setup lang="ts">
import "@videojs/html/video/player";
import "@videojs/html/video/skin";
import { computed, ref } from "vue";
import { isPlayerKey, videoAspect, videoBoxStyle } from "@/v8/utils/videoPlayer";

/**
 * Video player of the v8 lightbox: the default Video.js skin around a native
 * `<video>`. Loaded as a lazy chunk by `PhotoBox`, which mounts it again for
 * every video (a `<video>` does not reload when its source changes).
 *
 * The root fills the area given to it by the `class` of the parent; the skin
 * is the largest box of the video's ratio inside it.
 */
const props = defineProps<{
	src: string;
	/** Dimensions of the original, 0 when unknown: the metadata then give the ratio. */
	width: number;
	height: number;
	autoplay: boolean;
	loop: boolean;
}>();

const metadataAspect = ref<number | undefined>(undefined);
const aspect = computed(() => videoAspect(props.width, props.height) ?? metadataAspect.value);
const skinStyle = computed(() => ({ ...videoBoxStyle(aspect.value), "--media-accent-color": "var(--ui-primary)" }));

function onLoadedMetadata(event: Event) {
	const video = event.currentTarget as HTMLVideoElement;
	metadataAspect.value = videoAspect(video.videoWidth, video.videoHeight);
}

// The player's hotkeys work while it has the focus; the gallery shortcuts listen on the window.
function onKeydown(event: KeyboardEvent) {
	if (isPlayerKey(event)) {
		event.stopPropagation();
	}
}
</script>
