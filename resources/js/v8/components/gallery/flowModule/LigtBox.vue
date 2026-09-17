<template>
	<!--
		`fixed`, not `absolute`: the v2 render path's wrapper is `h-svh
		overflow-y-auto` (self-scrolling, always viewport-aligned), so
		`absolute top-0`/`h-full` there happens to fill the viewport either
		way - but the v3 (SoA) path has no such wrapper (`useWindowVirtualizer`
		requires letting `window` scroll directly, Flow.vue's own comment on
		its v3 root explains why), so the page - and this component's
		`position: relative` ancestor (`App.vue`'s `<main>`) - grows as tall as
		the whole virtualized album list. `absolute` then anchors to the top
		of that whole (scrolled-past) page instead of the visible viewport,
		leaving only this backdrop on-screen while the actual centered photo
		renders off-screen near the document's true top. `fixed` always
		covers the real viewport regardless of page height/scroll - exactly
		what `PhotoPanel.vue`'s `UModal fullscreen` (Timeline.vue's/Album.vue's
		own opened-photo view) already gets for free via its teleport.
	-->
	<div v-if="photoStore.photo" class="fixed z-20 top-0 left-0 w-full flex h-full overflow-hidden bg-black">
		<UButton color="neutral" variant="ghost" icon="lucide:x" class="absolute top-2 left-2 rounded-full" @click="emits('goBack')" />
		<div class="animate-zoomIn w-full h-full">
			<Transition name="fade">
				<PhotoBox @go-back="emits('goBack')" @next="emits('next')" @previous="emits('previous')" @click="emits('next')" />
			</Transition>
			<Overlay v-if="!is_exif_disabled && photoStore.imageViewMode !== ImageViewMode.Pdf" :photo="photoStore.photo" />
		</div>
	</div>
</template>
<script setup lang="ts">
import Overlay from "@/v8/components/gallery/photoModule/Overlay.vue";
import PhotoBox from "@/v8/components/gallery/photoModule/PhotoBox.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { ImageViewMode, usePhotoStore } from "@/stores/PhotoState";

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const { is_exif_disabled } = storeToRefs(lycheeStore);

const emits = defineEmits<{
	goBack: [];
	next: [];
	previous: [];
}>();
</script>
<style>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s ease-in-out;
}
.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
	opacity: 1;
}
</style>
