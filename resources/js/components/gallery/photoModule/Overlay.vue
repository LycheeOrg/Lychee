<template>
	<div id="image_overlay" class="absolute bottom-7 left-7 text-shadow text-white" v-if="props.imageOverlayType !== 'none'">
		<h1 class="text-xl sm:text-3xl text-surface-200" x-text="photo.title">{{ props.photo.title }}</h1>
		<p
			class="mt-1 text-base sm:text-xl text-surface-400 prose prose-invert"
			v-if="props.imageOverlayType === 'desc'"
			v-html="props.photo.preformatted.description"
		></p>
		<p class="mt-1 text-base sm:text-xl text-surface-400" v-if="props.imageOverlayType === 'date'">
			<MiniIcon v-if="props.photo.precomputed.is_camera_date" icon="camera-slr" class="w-4 h-4 m-0 mr-1 -mt-1 fill-surface-400" />{{
				props.photo.preformatted.date_overlay
			}}<span v-if="props.photo.precomputed.is_taken_at_modified" class="text-warning-600">*</span>
		</p>
		<p class="mt-1 text-base sm:text-xl text-surface-400" v-if="props.imageOverlayType === 'exif' && props.photo.precomputed.is_video">
			{{ props.photo.preformatted.duration }} at {{ props.photo.preformatted.fps }} fps
		</p>
		<p
			class="mt-1 text-base sm:text-xl text-surface-400"
			v-if="props.imageOverlayType === 'exif' && !props.photo.precomputed.is_video && props.photo.preformatted.shutter !== ''"
		>
			{{ props.photo.preformatted.shutter }} at &fnof; / {{ props.photo.preformatted.aperture }}, {{ props.photo.preformatted.iso }}
			<br />
			{{ props.photo.focal }} {{ props.photo.preformatted.lens }}
		</p>
	</div>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
	imageOverlayType: App.Enum.ImageOverlayType;
}>();
</script>
