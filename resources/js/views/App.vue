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
