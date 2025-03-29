<template>
	<Button @click="openLeftMenu" icon="pi pi-bars" class="mr-2 border-none" severity="secondary" text />
</template>
<script setup lang="ts">
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";

const leftMenuStore = useLeftMenuStateStore();
const { left_menu_open } = storeToRefs(leftMenuStore);
const openLeftMenu = () => (left_menu_open.value = !left_menu_open.value);
</script>
