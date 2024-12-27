<template>
	<Toast />
	<Error />
	<LeftMenu />
	<main>
		<router-view />
	</main>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import Toast from "primevue/toast";
import LeftMenu from "@/menus/LeftMenu.vue";
import Error from "@/views/Error.vue";
import { disableCtrlA } from "@/utils/keybindings-utils";
disableCtrlA();

onMounted(() => {
	document.getElementsByClassName("LycheeLoading")[0].classList.add("!hidden");
});
</script>

<style lang="scss">
body {
	background-color: var(--p-surface-0);

	&.dark {
		background-color: var(--p-surface-900);
	}
}
</style>
