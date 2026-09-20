<template>
	<div id="leaflet_map_single_photo" class="h-48 my-0.5"></div>
</template>
<script setup lang="ts">
import { useSidebarMap } from "@/services/sidebar-map";
import { onMounted, watch } from "vue";

const props = defineProps<{
	latitude: number | null;
	longitude: number | null;
}>();
const { latitude, longitude, load, onMount } = useSidebarMap(props.latitude, props.longitude);

onMounted(() => {
	onMount();
});

watch(
	() => [props.latitude, props.longitude],
	([newlatitude, newlongitude], [_oldlatitude, _oldlongitude]) => {
		latitude.value = newlatitude;
		longitude.value = newlongitude;
		load();
	},
);
</script>
