<template>
	<div class="absolute z-20 top-0 left-0 w-full flex h-full overflow-hidden bg-black">
		<Button severity="secondary" text icon="pi pi-times" rounded class="absolute top-2 left-2 border-none" @click="emits('goBack')"></Button>
		<div class="animate-zoomIn w-full h-full">
			<Transition name="fade">
				<PhotoBox
					:key="photo.id"
					:photo="props.photo"
					@go-back="emits('goBack')"
					@next="emits('next')"
					@previous="emits('previous')"
					@click="emits('next')"
				/>
			</Transition>
			<Overlay v-if="!is_exif_disabled && imageViewMode !== ImageViewMode.Pdf" :photo="photo" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import Overlay from "@/components/gallery/photoModule/Overlay.vue";
import PhotoBox from "@/components/gallery/photoModule/PhotoBox.vue";
import { usePhotoBaseFunction, ImageViewMode } from "@/composables/photo/basePhoto";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const lycheeStore = useLycheeStateStore();
const { is_exif_disabled } = storeToRefs(lycheeStore);

const photo = computed(() => props.photo);

const { imageViewMode } = usePhotoBaseFunction(photo);

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
