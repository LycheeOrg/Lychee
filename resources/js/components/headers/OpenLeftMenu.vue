<template>
	<Button @click="openLeftMenu" icon="pi pi-bars" class="mr-2 border-none" severity="secondary" text />
</template>
<script setup lang="ts">
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";

const togglableStore = useTogglablesStateStore();
const { left_menu_open } = storeToRefs(togglableStore);
const openLeftMenu = () => (left_menu_open.value = !left_menu_open.value);
</script>
