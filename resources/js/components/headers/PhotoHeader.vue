<template>
	<header
		id="lychee_toolbar_container"
		class="absolute top-0 left-0 w-full flex-none z-10 bg-gradient-to-b from-black h-14"
		:class="is_full_screen || is_slideshow_active ? 'opacity-0 hover:opacity-100' : 'opacity-100 h-14'"
	>
		<Toolbar class="w-full bg-transparent border-0">
			<template #start>
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
			</template>
			<template #end>
				<div :class="is_slideshow_active ? 'hidden' : 'flex'">
					<Button text icon="pi pi-play" class="mr-2" severity="secondary" @click="toggleSlideShow" />
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
					<Button v-if="props.photo.rights.can_edit" text icon="pi pi-pencil" class="mr-2" severity="secondary" @click="toggleEdit" />
					<Button icon="pi pi-info" class="mr-2" severity="secondary" text @click="toggleDetails" />
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
import { useRouter } from "vue-router";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import DownloadPhoto from "../modals/DownloadPhoto.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const router = useRouter();
const emits = defineEmits<{
	slideshow: [];
}>();

const props = defineProps<{
	albumid: string;
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const togglableStore = useTogglablesStateStore();
const { is_full_screen, is_edit_open, are_details_open, is_slideshow_active } = storeToRefs(togglableStore);
const isDownloadOpen = ref(false);

onKeyStroke("i", () => !shouldIgnoreKeystroke() && toggleDetails());
onKeyStroke("e", () => !shouldIgnoreKeystroke() && props.photo.rights.can_edit && toggleEdit());

function goBack() {
	if (togglableStore.isSearchActive && !togglableStore.search_album_id) {
		router.push({ name: "search" });
		return;
	}
	if (togglableStore.isSearchActive) {
		router.push({ name: "search-with-album", params: { albumid: togglableStore.search_album_id } });
		return;
	}
	router.push({ name: "album", params: { albumid: props.albumid } });
}

function toggleDetails() {
	are_details_open.value = !are_details_open.value;
}

function toggleEdit() {
	is_edit_open.value = !is_edit_open.value;
}

function toggleSlideShow() {
	emits("slideshow");
}

function openInNewTab(url: string) {
	window?.open(url, "_blank")?.focus();
}

// on key stroke escape:
// 1. lose focus
// 2. close modals
// 3. go back
onKeyStroke("Escape", () => {
	if (is_slideshow_active.value) {
		is_slideshow_active.value = false;
		return;
	}

	// 1. lose focus
	if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
		return;
	}

	if (are_details_open.value) {
		toggleDetails();
		return;
	}

	goBack();
});
</script>
