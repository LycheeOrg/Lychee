<template>
	<Toast />
	<Error />
	<LeftMenu />
	<main>
		<router-view />
	</main>
</template>

<script setup lang="ts">
import Toast from "primevue/toast";
import LeftMenu from "@/menus/LeftMenu.vue";
import Error from "@/views/Error.vue";
import { disableCtrlA } from "@/utils/keybindings-utils";

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
