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
</script>

<style lang="scss">
body {
	background-color: var(--p-surface-0);

	&.dark {
		background-color: var(--p-surface-900);
	}
}
</style>
