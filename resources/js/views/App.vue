<template>
	<Toast />
	<Error />
	<LeftMenu />
	<main>
		<router-view />
	</main>
</template>

<script setup lang="ts">
import LeftMenu from "@/menus/LeftMenu.vue";
import Error from "@/views/Error.vue";
import Toast from "primevue/toast";
</script>

<style lang="scss">
body {
	background-color: var(--p-surface-0);

	&.lychee-dark {
		background-color: var(--p-surface-900);
	}
}

//   font-family: var(--font-family);
//   font-weight: 400;
//   color: var(--text-color);</style>
