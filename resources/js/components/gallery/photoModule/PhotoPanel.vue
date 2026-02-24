<template>
	<div v-if="photoStore.photo" class="absolute z-20 top-0 left-0 w-full flex h-full overflow-hidden bg-black">
		<PhotoHeader @toggle-slide-show="emits('toggleSlideShow')" @go-back="emits('goBack')" @toggle-highlight="emits('toggleHighlight')" />
		<div class="w-0 flex-auto relative">
			<div class="animate-zoomIn w-full h-full">
				<Transition :name="photoStore.transition">
					<PhotoBox
						:key="photoStore.photo.id"
						@go-back="emits('goBack')"
						@next="emits('next')"
						@previous="emits('previous')"
						@rotate-overlay="emits('rotateOverlay')"
					/>
				</Transition>
			</div>
			<NextPrevious
				v-if="photoStore.photo.previous_photo_id !== null && !is_slideshow_active"
				:photo-id="photoStore.photo.previous_photo_id"
				:is_next="false"
				:style="photoStore.previousStyle"
			/>
			<NextPrevious
				v-if="photoStore.photo.next_photo_id !== null && !is_slideshow_active"
				:photo-id="photoStore.photo.next_photo_id"
				:is_next="true"
				:style="photoStore.nextStyle"
			/>
			<Overlay v-if="!is_exif_disabled && photoStore.imageViewMode !== ImageViewMode.Pdf" />
			<PhotoRatingOverlay />
			<Dock
				v-if="albumStore.rights?.can_edit && !is_photo_edit_open"
				:is-narrow-menu="photoStore.imageViewMode === ImageViewMode.Pdf"
				@toggle-star="emits('toggleHighlight')"
				@set-album-header="emits('setAlbumHeader')"
				@rotate-photo-c-c-w="emits('rotatePhotoCCW')"
				@rotate-photo-c-w="emits('rotatePhotoCW')"
				@toggle-move="emits('toggleMove')"
				@toggle-delete="emits('toggleDelete')"
			/>
		</div>
		<PhotoDetails v-if="!is_exif_disabled" v-model:are-details-open="are_details_open" :is-map-visible="props.isMapVisible" />
	</div>
</template>
<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { onMounted } from "vue";
import NextPrevious from "./NextPrevious.vue";
import Overlay from "./Overlay.vue";
import PhotoRatingOverlay from "./PhotoRatingOverlay.vue";
import PhotoDetails from "@/components/drawers/PhotoDetails.vue";
import PhotoHeader from "@/components/headers/PhotoHeader.vue";
import Dock from "./Dock.vue";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onUnmounted } from "vue";
import { useDebounceFn } from "@vueuse/core";
import PhotoBox from "./PhotoBox.vue";
import { ImageViewMode, usePhotoStore } from "@/stores/PhotoState";
import { useAlbumStore } from "@/stores/AlbumState";

const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const photoStore = usePhotoStore();
const albumStore = useAlbumStore();

const { is_exif_disabled, is_scroll_to_navigate_photos_enabled } = storeToRefs(lycheeStore);
const { is_photo_edit_open, is_slideshow_active, are_details_open } = storeToRefs(togglableStore);

const props = defineProps<{
	isMapVisible: boolean;
}>();

const emits = defineEmits<{
	toggleHighlight: [];
	setAlbumHeader: [];
	rotatePhotoCCW: [];
	rotatePhotoCW: [];
	toggleMove: [];
	toggleDelete: [];
	updated: [];
	rotateOverlay: [];
	toggleSlideShow: [];
	goBack: [];
	next: [];
	previous: [];
}>();

// We use debounce to avoid multiple skipping too many pictures in one go via the trackpad
function scrollTo(event: WheelEvent) {
	if (shouldIgnoreKeystroke()) {
		return;
	}

	if (is_photo_edit_open.value) {
		// We do nothing! Otherwise we are switching photos without noticing.
		// especially with trackpads.
		return;
	}

	if (!is_scroll_to_navigate_photos_enabled.value) {
		return;
	}

	const delta = Math.sign(event.deltaY);
	if (delta > 0) {
		emits("next");
	} else if (delta < 0) {
		emits("previous");
	}
}
const debouncedScrollTo = useDebounceFn(scrollTo, 10);

onMounted(() => {
	window.addEventListener("wheel", debouncedScrollTo);
});

onUnmounted(() => {
	window.removeEventListener("wheel", debouncedScrollTo);
});
</script>

<style lang="css">
[dir="ltr"] {
	--next-enter-from: 5%;
	--next-leave-to: -5%;

	--previous-enter-from: -5%;
	--previous-leave-to: 5%;
}

[dir="rtl"] {
	--next-enter-from: -5%;
	--next-leave-to: 5%;

	--previous-enter-from: 5%;
	--previous-leave-to: -5%;
}
.slide-next-leave-active,
.slide-next-enter-active {
	transition:
		transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1),
		opacity 0.2s cubic-bezier(0.445, 0.05, 0.55, 0.95);
}
.slide-next-enter-from {
	transform: translate(var(--next-enter-from), 0);
	opacity: 0;
}
.slide-next-enter-to,
.slide-next-leave-from {
	transform: translate(0, 0);
	opacity: 1;
}
.slide-next-leave-to {
	transform: translate(var(--next-leave-to), 0);
	opacity: 0;
}

.slide-previous-leave-active,
.slide-previous-enter-active {
	transition:
		transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1),
		opacity 0.2s cubic-bezier(0.445, 0.05, 0.55, 0.95);
}
.slide-previous-enter-from {
	transform: translate(var(--previous-enter-from), 0);
	opacity: 0;
}
.slide-previous-enter-to,
.slide-previous-leave-from {
	transform: translate(0, 0);
	opacity: 1;
}
.slide-previous-leave-to {
	transform: translate(var(--previous-leave-to), 0);
	opacity: 0;
}
</style>
