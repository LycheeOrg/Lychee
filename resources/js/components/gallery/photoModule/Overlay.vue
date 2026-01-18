<template>
	<div
		v-if="photoStore.photo !== undefined && image_overlay_type !== 'none'"
		id="image_overlay"
		class="absolute bottom-7 ltr:left-7 rtl:right-7 text-shadow text-white pointer-events-none"
	>
		<h1 class="text-xl sm:text-3xl text-surface-200" x-text="photo.title">{{ photoStore.photo.title }}</h1>
		<p
			v-if="image_overlay_type === 'desc'"
			class="mt-1 text-base sm:text-xl text-surface-400 prose prose-invert"
			v-html="photoStore.photo.preformatted.description"
		></p>
		<p v-if="image_overlay_type === 'date'" dir="ltr" class="mt-1 text-base sm:text-xl text-surface-400 rtl:text-right ltr:text-left">
			<MiniIcon v-if="photoStore.photo.precomputed.is_camera_date" icon="camera-slr" class="w-4 h-4 m-0 mr-1 -mt-1 fill-surface-400" />{{
				photoStore.photo.preformatted.date_overlay
			}}<span v-if="photoStore.photo.precomputed.is_taken_at_modified" class="text-warning-600">*</span>
		</p>
		<p
			v-if="image_overlay_type === 'exif' && photoStore.photo.precomputed.is_video"
			dir="ltr"
			class="mt-1 text-base sm:text-xl text-surface-400 rtl:text-right ltr:text-left"
		>
			{{ photoStore.photo.preformatted.duration }} at {{ photoStore.photo.preformatted.fps }} fps
		</p>
		<p
			v-if="image_overlay_type === 'exif' && !photoStore.photo.precomputed.is_video && photoStore.photo.preformatted.shutter !== ''"
			dir="ltr"
			class="mt-1 text-base sm:text-xl text-surface-400 rtl:text-right ltr:text-left"
		>
			{{ photoStore.photo.preformatted.shutter }} at &fnof; / {{ photoStore.photo.preformatted.aperture }},
			{{ photoStore.photo.preformatted.iso }}
			<br />
			{{ photoStore.photo.preformatted.focal }} {{ photoStore.photo.preformatted.lens ? "(" + photoStore.photo.preformatted.lens + ")" : "" }}
		</p>
	</div>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { image_overlay_type } = storeToRefs(lycheeStore);

const photoStore = usePhotoStore();
</script>
