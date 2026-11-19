<template>
	<Toast />
	<ConfirmDialog />
	<Error />
	<GalleryUnlock v-if="isGalleryLocked" />
	<template v-else>
		<LeftMenu />
		<EmbedCodeDialog />
		<main>
			<router-view />
		</main>
	</template>
</template>

<script setup lang="ts">
import Toast from "primevue/toast";
import LeftMenu from "@/v7/menus/LeftMenu.vue";
import Error from "@/v7/views/Error.vue";
import EmbedCodeDialog from "@/v7/components/forms/album/EmbedCodeDialog.vue";
import { disableCtrlA } from "@/utils/keybindings-utils";
import { useDocumentTitle } from "@/composables/useDocumentTitle";
import ConfirmDialog from "primevue/confirmdialog";
import GalleryUnlock from "@/v7/views/GalleryUnlock.vue";
import { useGalleryLock } from "@/composables/useGalleryLock";

disableCtrlA();
useDocumentTitle();
const { isGalleryLocked } = useGalleryLock();
</script>

<style lang="css">
body {
	background-color: var(--p-surface-0);
}

body.dark {
	background-color: var(--p-surface-900);
}
</style>
