<template>
	<header
		id="lychee_toolbar_container"
		class="absolute top-0 left-0 w-full flex-none z-10 bg-gradient-to-b from-black h-14"
		:class="is_full_screen ? 'opacity-0 hover:opacity-100' : 'opacity-100 h-14'"
	>
		<Toolbar class="w-full bg-transparent border-0">
			<template #start>
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
			</template>
			<template #end>
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
// import ContextMenu from "primevue/contextmenu";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import DownloadPhoto from "../modals/DownloadPhoto.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const router = useRouter();
const props = defineProps<{
	albumid: string;
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_full_screen, is_edit_open, are_details_open } = storeToRefs(lycheeStore);
// const isEditOpen = defineModel("isEditOpen", { default: false });
// const areDetailsOpen = defineModel("areDetailsOpen", { default: false });
const isDownloadOpen = ref(false);

// const emit = defineEmits<{
// 	(e: "refresh"): void;
// }>();

// const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
onKeyStroke("i", () => !shouldIgnoreKeystroke() && toggleDetails());
onKeyStroke("e", () => !shouldIgnoreKeystroke() && props.photo.rights.can_edit && toggleEdit());

function goBack() {
	if (lycheeStore.isSearchActive && !lycheeStore.search_album_id) {
		router.push({ name: "search" });
		return;
	}
	if (lycheeStore.isSearchActive) {
		router.push({ name: "search-album", params: { albumid: lycheeStore.search_album_id } });
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

function openInNewTab(url: string) {
	window?.open(url, "_blank")?.focus();
}

// bubble up.
// function refresh() {
// 	emit("refresh");
// }

// on key stroke escape:
// 1. lose focus
// 2. close modals
// 3. go back
onKeyStroke("Escape", () => {
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
