<template>
	<Toast />
	<Drawer v-model:visible="leftMenuOpen">
		<LeftMenu />
	</Drawer>
	<main>
		<RouterView @toggleLeftMenu="toggleLeftMenu" />
	</main>
</template>

<script setup lang="ts">
import LeftMenu from "@/menus/LeftMenu.vue";
import Drawer from "primevue/drawer";
import { ref } from "vue";
import Toast from "primevue/toast";

const leftMenuOpen = ref(false);

function toggleLeftMenu() {
	leftMenuOpen.value = !leftMenuOpen.value;
}
</script>
