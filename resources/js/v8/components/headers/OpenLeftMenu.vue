<template>
	<UButton icon="prime:bars" color="neutral" variant="ghost" class="ltr:mr-2 rtl:ml-2" @click="openLeftMenu" />
</template>
<script setup lang="ts">
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";

const leftMenuStore = useLeftMenuStateStore();
const { left_menu_open } = storeToRefs(leftMenuStore);
const openLeftMenu = () => {
	left_menu_open.value = !left_menu_open.value;
};
</script>
