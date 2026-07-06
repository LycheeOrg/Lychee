<template>
	<Toast />
	<ConfirmDialog />
	<Error />
	<LeftMenu />
	<EmbedCodeDialog />
	<main>
		<router-view />
	</main>
</template>

<script setup lang="ts">
import Toast from "primevue/toast";
import LeftMenu from "@/v7/menus/LeftMenu.vue";
import Error from "@/v7/views/Error.vue";
import EmbedCodeDialog from "@/v7/components/forms/album/EmbedCodeDialog.vue";
import { disableCtrlA } from "@/utils/keybindings-utils";
import ConfirmDialog from "primevue/confirmdialog";

disableCtrlA();
</script>

<style lang="css">
body {
	background-color: var(--p-surface-0);
}

body.dark {
	background-color: var(--p-surface-900);
}
</style>
