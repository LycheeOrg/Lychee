<template>
	<header
		id="lychee_toolbar_container"
		:class="{
			'absolute top-0 left-0 w-full flex-none z-10 bg-gradient-to-b from-black h-14': true,
			'opacity-100 md:opacity-0 md:hover:opacity-100': is_full_screen || is_slideshow_active,
			'opacity-100 h-14': !is_full_screen && !is_slideshow_active,
		}"
	>
		<Toolbar class="w-full bg-transparent border-0">
			<template #start>
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="emits('goBack')" />
			</template>
			<template #end>
				<div :class="is_slideshow_active ? 'hidden' : 'flex'">
					<Button text icon="pi pi-play" class="mr-2" severity="secondary" @click="emits('toggleSlideShow')" />
					<Button
						v-if="props.photo.rights.can_access_full_photo && props.photo.size_variants.original?.url"
						text
						icon="pi pi-window-maximize"
						class="mr-2 font-bold"
						severity="secondary"
						@click="openInNewTab(props.photo.size_variants.original.url)"
					/>
					<Button
						v-if="props.photo.rights.can_download"
						text
						icon="pi pi-cloud-download"
						class="mr-2"
						severity="secondary"
						@click="isDownloadOpen = !isDownloadOpen"
					/>
					<Button
						v-if="props.photo.rights.can_edit"
						text
						icon="pi pi-pencil"
						class="mr-2"
						severity="secondary"
						@click="is_photo_edit_open = !is_photo_edit_open"
					/>
					<Button
						icon="pi pi-info"
						class="mr-2"
						severity="secondary"
						text
						@click="are_details_open = !are_details_open"
						v-if="!is_exif_disabled"
					/>
				</div>
			</template>
		</Toolbar>
	</header>
	<DownloadPhoto :photo="props.photo" v-model:visible="isDownloadOpen" />
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { ref } from "vue";
import DownloadPhoto from "../modals/DownloadPhoto.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLycheeStateStore } from "@/stores/LycheeState";

const emits = defineEmits<{
	toggleDetails: [];
	toggleEdit: [];
	toggleSlideShow: [];
	goBack: [];
}>();

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const togglableStore = useTogglablesStateStore();
const { is_full_screen, is_photo_edit_open, are_details_open, is_slideshow_active } = storeToRefs(togglableStore);
const isDownloadOpen = ref(false);
const lycheeStore = useLycheeStateStore();
const { is_exif_disabled } = storeToRefs(lycheeStore);

function openInNewTab(url: string) {
	window?.open(url, "_blank")?.focus();
}
</script>
